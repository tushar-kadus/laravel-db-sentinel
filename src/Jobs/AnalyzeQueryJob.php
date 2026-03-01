<?php

namespace Atmos\DbSentinel\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Atmos\DbSentinel\Services\QueryExplainer;
use Atmos\DbSentinel\Services\QueryAnalyzer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Throwable;

class AnalyzeQueryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the log record to analyze.
     */
    public $logId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $logId)
    {
        $this->logId = $logId;
    }

    /**
     * Execute the job.
     */
    public function handle(QueryExplainer $explainer, QueryAnalyzer $analyzer): void
    {
        try {
            // Retrieve the record using the Model
            $log = SentinelLog::find($this->logId);

            if (!$log) return;

            // Perform Explanation (EXPLAIN ANALYZE)
            $log->explanation = $explainer->explain($log);
            // throw new \Exception("Error Processing Request", 1);

            // Generate Suggestions
            $analyzer->analyze($log);

            info('ANALYZED ... : ' . $log->id);
            $log->status = 'analyzed';
        } catch (Throwable $e) {
            $log->explanation = $e->getMessage();
            $log->status = 'failed';
        } finally {
            // Update the record
            $log->saveQuietly();
        }
    }
}
