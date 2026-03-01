<?php

namespace Atmos\DbSentinel\Heuristics;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckFullTableScan extends Heuristic
{
    public function handle(SentinelLog $log, Closure $next)
    {
        $explanation = $log->explanation ?: [];
        $driver = DB::connection($log->connection)->getDriverName();
        
        $scannedTables = [];

        // Collect ALL tables doing a full scan
        foreach ($explanation as $node) {
            if ($this->isFullScan($node, $driver)) {
                $tableName = $this->getTableName($node, $driver);
                $scannedTables[] = $tableName;
            }
        }

        // Remove duplicates (some DBs report the same table multiple times in subqueries)
        $uniqueTables = array_unique($scannedTables);

        if (!empty($uniqueTables)) {
            $tableList = implode("', '", $uniqueTables);
            $count = count($uniqueTables);
            
            $message = ($count > 1) 
                ? "Multiple tables ('{$tableList}') are performing full scans. This is extremely heavy."
                : "The table '{$tableList}' is performing a full scan.";

            $suggestion = Suggestion::make('Full Table Scan Detected', $message);

            if ($count >= 2) { // Multiple tables doing full scans is a disaster
                $suggestion->critical(); 
            } elseif ($count === 1) {
                $suggestion->high();
            }

            $this->addSuggestion($log, $suggestion);
        }

        return $next($log);
    }

    /**
     * Detects if a specific node in the execution plan represents a Full Table Scan.
     * Each database driver has a unique way of identifying this operation.
     */
    protected function isFullScan(array $node, string $driver): bool
    {
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                /**
                 * MySQL 'type' column indicates the join type. 
                 * 'ALL' means the engine must scan the entire table to find matches.
                 */
                return isset($node['type']) && strtoupper($node['type']) === 'ALL';

            case 'pgsql':
                /**
                 * PostgreSQL uses 'Node Type'. 
                 * 'Seq Scan' stands for Sequential Scan, which reads the table 
                 * from beginning to end without using an index.
                 */
                return isset($node['Node Type']) && $node['Node Type'] === 'Seq Scan';

            case 'sqlite':
                /**
                 * SQLite 'detail' contains a human-readable string.
                 * We look for 'SCAN TABLE' which indicates no index was applied.
                 */
                return isset($node['detail']) && strpos($node['detail'], 'SCAN TABLE') !== false;

            case 'sqlsrv':
                /**
                 * SQL Server (MSSQL) uses 'PhysicalOp' (Physical Operation).
                 * 'Table Scan' occurs on heaps, while 'Clustered Index Scan' 
                 * is effectively a full table scan on indexed tables.
                 */
                $op = $node['PhysicalOp'] ?? '';
                return $op === 'Table Scan' || $op === 'Clustered Index Scan';

            default:
                /**
                 * Fallback: Many smaller or generic drivers follow the 
                 * MySQL 'type' => 'ALL' convention.
                 */
                return isset($node['type']) && strtoupper($node['type']) === 'ALL';
        }
    }

    /**
     * Detect table name
     */
    protected function getTableName(array $node, string $driver): string
    {
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                // MySQL usually provides the table name in the 'table' key
                return $node['table'] ?? 'unknown_table';

            case 'pgsql':
                /** * PostgreSQL EXPLAIN (JSON) uses 'Relation Name'. 
                 * If it's an alias, it uses 'Alias'.
                 */
                return $node['Relation Name'] ?? $node['Alias'] ?? 'unknown_table';

            case 'sqlsrv':
                // SQL Server execution plans often use 'Object' or 'Argument'
                return $node['Object'] ?? $node['Argument'] ?? 'unknown_table';

            case 'sqlite':
                /**
                 * SQLite is tricky; the table name is often embedded in the 'detail' string:
                 * "SCAN TABLE users"
                 */
                if (isset($node['detail']) && preg_match('/SCAN TABLE\s+(\w+)/i', $node['detail'], $matches)) {
                    return $matches[1];
                }
                return 'unknown_table';

            default:
                // Fallback for unknown drivers
                return $node['table'] ?? 'unknown_table';
        }
    }
}
