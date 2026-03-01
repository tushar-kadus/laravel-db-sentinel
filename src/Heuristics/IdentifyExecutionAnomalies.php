<?php

namespace Atmos\DbSentinel\Heuristics;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Support\Facades\DB;
use Closure;

class IdentifyExecutionAnomalies extends Heuristic
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
                $extra = $node['Extra'] ?? '';
                if (str_contains($extra, 'Using filesort')) {
                    $this->addSuggestion($log, Suggestion::make('Unindexed Sort', 'Database is performing a manual sort (filesort) because no index could satisfy the ORDER BY clause.')->high());
                }
                if (str_contains($extra, 'Using temporary')) {
                    $this->addSuggestion($log, Suggestion::make('Internal Temporary Table', 'A temporary table was created to resolve this query.')->critical());
                }
                break;

            case 'pgsql':
                // PostgreSQL puts anomalies in 'Filter' or 'Sort Method'
                if (isset($node['Filter'])) {
                    $this->addSuggestion($log, Suggestion::make('Post-Scan Filtering', 'Rows are being filtered after the scan (Filter). This usually indicates a missing index for the WHERE clause.')->medium());
                }
                if (isset($node['Sort Method']) && str_contains($node['Sort Method'], 'external merge')) {
                    $this->addSuggestion($log, Suggestion::make('Disk-Based Sort', 'The sort operation was too large for memory and spilled to disk (external merge).')->critical());
                }
                break;

            case 'sqlite':
                // SQLite provides anomalies in the 'detail' string
                $detail = $node['detail'] ?? '';
                if (str_contains($detail, 'USE TEMP B-TREE')) {
                    $this->addSuggestion($log, Suggestion::make('Temporary B-Tree', 'SQLite is building a temporary index in memory/disk to handle a GROUP BY or ORDER BY.')->high());
                }
                break;

            case 'sqlsrv':
                // SQL Server uses 'Warnings' for major anomalies
                if (isset($node['Warnings'])) {
                    $this->addSuggestion($log, Suggestion::make('SQL Server Warning', $node['Warnings'])->high());
                }
                break;
        }
    }
}
