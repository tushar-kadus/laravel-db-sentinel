<?php

namespace Atmos\DbSentinel\Console\Commands;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Console\Command;

class PruneLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-sentinel:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old DB Sentinel logs based on the retention policy';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = config('db-sentinel.database.prune_after_days', 30);

        $this->info("Pruning logs older than {$days} days...");

        $deleted = SentinelLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Successfully deleted {$deleted} old log entries.");

        return Command::SUCCESS;
    }
}
