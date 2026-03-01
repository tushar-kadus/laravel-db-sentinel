<?php

namespace Atmos\DbSentinel\Heuristics;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Support\Facades\DB;
use Closure;

class IdentifyCartesianProducts extends Heuristic
{
    public function handle(SentinelLog $log, Closure $next)
    {
        $explanation = $log->explanation ?: [];
        $driver = DB::connection($log->connection)->getDriverName();

        foreach ($explanation as $node) {
            $this->analyzeNode($log, $node, $driver);
        }

        return $next($log);
    }

    protected function analyzeNode(SentinelLog $log, array $node, string $driver): void
    {
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $type = $node['type'] ?? '';
                $ref = $node['ref'] ?? '';
                $extra = $node['Extra'] ?? '';
                $table = $node['table'] ?? '';

                // A Cartesian product occurs when:
                // 1. It's not the first table in the plan (first table is usually 'ALL' anyway)
                // 2. The join type is 'ALL' (Full Table Scan)
                // 3. There is no 'ref' (no column being used to join)
                // 4. The 'Extra' column mentions a Join Buffer

                $isFullScan = ($type === 'ALL');
                $noJoinKey = ($ref === 'NULL' || empty($ref) || $ref === null);
                $usesJoinBuffer = str_contains($extra, 'join buffer');

                if ($isFullScan && $noJoinKey && $usesJoinBuffer) {
                    $this->addSuggestion($log, Suggestion::make(
                        'Cartesian Product (Hash Join)', 
                        "The table '{$table}' is being joined using a hash join buffer because no join condition or index was found. This will result in {$log->rows_examined} potential row combinations."
                    )->critical());
                }
                break;

            case 'pgsql':
                /**
                 * PostgreSQL explicitly names the 'Node Type' as 'Nested Loop'
                 * but if it lacks a 'Join Filter', it's a Cartesian Product.
                 */
                $nodeType = $node['Node Type'] ?? '';
                $hasJoinFilter = isset($node['Join Filter']);
                $hasIndexCond = isset($node['Index Cond']);

                if ($nodeType === 'Nested Loop' && !$hasJoinFilter && !$hasIndexCond) {
                    $this->addSuggestion($log, Suggestion::make('Cartesian Product', 'PostgreSQL is performing a Nested Loop join without a filter, which may lead to exponential row growth.')->critical());
                }
                break;

            case 'sqlite':
                /**
                 * SQLite 'SCAN' or 'SEARCH' without a 'USING' clause 
                 * on subsequent tables indicates a cross join.
                 */
                $detail = $node['detail'] ?? '';
                if (str_contains($detail, 'SCAN TABLE') && !str_contains($detail, 'USING')) {
                     // We check if this isn't the first table in the log to be sure
                     $this->addSuggestion($log, Suggestion::make('Possible Cross Join', 'SQLite is scanning a table without a join key, likely resulting in a Cartesian Product.')->high());
                }
                break;

            case 'sqlsrv':
                /**
                 * SQL Server identifies this as a 'Nested Loops' with a 
                 * 'Warning' of 'NoJoinPredicate'.
                 */
                if (isset($node['Warnings']) && str_contains($node['Warnings'], 'NoJoinPredicate')) {
                    $this->addSuggestion($log, Suggestion::make('Cartesian Product', 'SQL Server detected a join with no predicate (NoJoinPredicate).')->critical());
                }
                break;
        }
    }
}
