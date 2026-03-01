<?php

namespace Atmos\DbSentinel\Heuristics;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Support\Facades\DB;
use Closure;

/**
 * Heuristic: Detect Unused Indexes
 * * This class identifies queries where the database engine identified potential 
 * indexes that could satisfy the query but ultimately decided to perform 
 * a more expensive scan instead.
 */
class DetectUnusedIndexes extends Heuristic
{
    /**
     * Handle the heuristic analysis.
     * * @param SentinelLog $log
     * @param Closure $next
     * @return mixed
     */
    public function handle(SentinelLog $log, Closure $next)
    {
        $explanation = $log->explanation ?: [];
        $driver = DB::connection($log->connection)->getDriverName();

        foreach ($explanation as $node) {
            if ($this->hasIgnoredIndex($node, $driver)) {
                $tableName = $this->getTableName($node, $driver);

                $this->addSuggestion($log, Suggestion::make(
                    'Potential Index Ignored',
                    "The database found usable indexes for table '{$tableName}' but performed a scan anyway. " .
                    'Check for type mismatches (e.g. string vs int) or functions wrapped around columns in your WHERE clause.'
                )->medium());
            }
        }

        return $next($log);
    }

    /**
     * Determine if a database engine ignored a valid index.
     */
    protected function hasIgnoredIndex(array $node, string $driver): bool
    {
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                /**
                 * MySQL/MariaDB: 
                 * 'possible_keys' lists candidates.
                 * 'key' is the chosen one.
                 */
                return !empty($node['possible_keys']) && empty($node['key']);

            case 'pgsql':
                /**
                 * PostgreSQL:
                 * Usually appears as a 'Seq Scan' even if 'Index Cond' was possible,
                 * or a 'Bitmap Heap Scan' that was rejected. 
                 * Note: PG JSON explain is deeply nested; this assumes a flattened node.
                 */
                return isset($node['Node Type']) && 
                       $node['Node Type'] === 'Seq Scan' && 
                       isset($node['Filter']);

            case 'sqlsrv':
                /**
                 * SQL Server:
                 * Look for 'MissingAddress' or 'MissingIndex' elements in the XML/JSON plan.
                 */
                return isset($node['MissingIndex']) || isset($node['Warnings']);

            case 'sqlite':
                /**
                 * SQLite:
                 * If 'detail' contains 'SCAN' instead of 'SEARCH' when a WHERE 
                 * clause is clearly present in the query.
                 */
                return isset($node['detail']) && 
                       strpos($node['detail'], 'SCAN TABLE') !== false &&
                       strpos($log->sql, 'WHERE') !== false;

            default:
                return false;
        }
    }

    /**
     * Get the table name from the node based on driver.
     */
    protected function getTableName(array $node, string $driver): string
    {
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                // MySQL/MariaDB consistently use the 'table' key.
                return $node['table'] ?? 'unknown_table';

            case 'pgsql':
                /**
                 * PostgreSQL (JSON format) uses 'Relation Name'. 
                 * If an alias is used in the query, it is stored in 'Alias'.
                 */
                return $node['Relation Name'] ?? $node['Alias'] ?? 'unknown_table';

            case 'sqlsrv':
                /**
                 * SQL Server plans are complex. Usually, the table is in 'Object' 
                 * or 'Argument' depending on the operation type.
                 */
                return $node['Object'] ?? $node['Argument'] ?? 'unknown_table';

            case 'sqlite':
                /**
                 * SQLite returns a string in 'detail'. Use regex to find
                 * the table name following 'SCAN TABLE' or 'SEARCH TABLE'.
                 */
                $detail = $node['detail'] ?? '';
                if (preg_match('/(?:SCAN|SEARCH) TABLE\s+([^\s\(\)]+)/i', $detail, $matches)) {
                    return $matches[1];
                }
                return 'unknown_table';

            default:
                // Generic fallback.
                return $node['table'] ?? $node['Relation Name'] ?? 'unknown_table';
        }
    }
}
