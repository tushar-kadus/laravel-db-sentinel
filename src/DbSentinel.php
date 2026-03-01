<?php

namespace Atmos\DbSentinel;

use Illuminate\Database\Events\QueryExecuted;
use Atmos\DbSentinel\Jobs\AnalyzeQueryJob;
use Atmos\DbSentinel\Models\SentinelLog;
use Atmos\DbSentinel\CaptureRulebook;

class DbSentinel
{
    /**
     * @var CaptureRulebook
     */
    protected $rulebook;

    public function __construct(CaptureRulebook $rulebook)
    {
        $this->rulebook = $rulebook;
    }

    /**
     * The entry point called by the Service Provider for every query.
     */
    public function listen(QueryExecuted $query): void
    {
        // Create CapturedQuery object for the query
        $capturedQuery = new CapturedQuery($query);

        // Run the query through the CaptureRulebook pipeline.
        if (!$this->rulebook->shouldCapture($capturedQuery)) {
            return;
        }

        // Perform the initial database write (The "Pending" log)
        $logId = $this->captureRecord($capturedQuery);

        // Send the ID to the queue for background EXPLAIN analysis
        AnalyzeQueryJob::dispatch($logId);
    }

    /**
     * Handles the database insertion for the initial record.
     */
    protected function captureRecord(CapturedQuery $capturedQuery): int
    {
        $log = new SentinelLog;
        $log->hash = $capturedQuery->hash;
        $log->connection = $capturedQuery->connection;
        $log->sql = $capturedQuery->sql;
        $log->bindings = $capturedQuery->bindings;
        $log->execution_time = $capturedQuery->executionTime;
        $log->url = $capturedQuery->url;
        $log->method = $capturedQuery->method;
        $log->caller = $capturedQuery->caller;
        $log->status = 'pending';
        // Save without triggering Eloquent events to avoid recursion
        $log->saveQuietly();

        return $log->id;
    }
}
