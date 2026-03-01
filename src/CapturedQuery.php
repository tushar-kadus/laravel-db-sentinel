<?php

namespace Atmos\DbSentinel;

use Illuminate\Database\Events\QueryExecuted;
use Atmos\DbSentinel\Support\SourceDetector;
use Illuminate\Support\Str;

class CapturedQuery
{
    public $hash;
    public $connection;
    public $sql;
    public $bindings;
    public $executionTime;
    public $url;
    public $method;
    public $caller;

    public function __construct(QueryExecuted $query)
    {
        $this->hash = $this->generateHash($query->sql);
        $this->connection = $query->connectionName;
        $this->sql = $query->sql;
        $this->bindings = $query->bindings;
        $this->executionTime = $query->time;
        $this->url = request()->fullUrl();
        $this->method = request()->method();
        $this->caller = SourceDetector::detect();
    }

    /**
     * SQL hash for fingerprinting.
     */
    protected function generateHash(string $sql): string
    {
        return md5(trim($sql));
    }
}
