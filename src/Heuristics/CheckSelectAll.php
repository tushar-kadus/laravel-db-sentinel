<?php

namespace Atmos\DbSentinel\Heuristics;

use Atmos\DbSentinel\Support\SqlWildcardInspector;
use Atmos\DbSentinel\Models\SentinelLog;
use Closure;

class CheckSelectAll extends Heuristic
{
    public function handle(SentinelLog $log, Closure $next)
    {
        if (SqlWildcardInspector::hasSelectAll($log->sql)) {
            $this->addSuggestion(
                $log,
                Suggestion::make(
                    'Wildcard Column Selection', 
                    "Using '*' or 'alias.*' fetches all columns. This prevents 'Covering Index' optimizations and increases memory usage."
                )->low()
            );
        }

        return $next($log);
    }
}
