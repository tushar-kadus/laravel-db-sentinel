<?php

namespace Atmos\DbSentinel\Services;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Exception;
use PDO;

class QueryExplainer
{
    public function explain(SentinelLog $log): array
    {
        try {
            $sql = $log->getRawOriginal('sql');
            $bindings = $log->bindings ?: [];
            $connection = DB::connection($log->connection);
            $driver = $connection->getDriverName();
            $explainQuery = $this->getExplainQuery($driver, $sql);
            $explaination = $connection->select($explainQuery, $bindings);

            $pdo = $connection->getPdo();
            $stmt = $pdo->prepare($explainQuery);
            $stmt->execute($bindings);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [['error' => 'Explain failed: ' . $e->getMessage()]];
        }
    }

    private function getExplainQuery(string $driver, string $sql): string
    {
        switch ($driver) {
            case 'mysql':
                return 'EXPLAIN ' . $sql;
            case 'pgsql':
                // PostgreSQL JSON format is easier for Heuristics to parse
                return 'EXPLAIN (FORMAT JSON) ' . $sql;
            case 'sqlite':
                return 'EXPLAIN QUERY PLAN ' . $sql;
            default:
                return 'EXPLAIN ' . $sql;
        }
    }
}
