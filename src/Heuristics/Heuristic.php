<?php

namespace Atmos\DbSentinel\Heuristics;

use Atmos\DbSentinel\Models\SentinelLog;
use Closure;

abstract class Heuristic
{
    /**
     * Handle the incoming query log.
     */
    abstract public function handle(SentinelLog $log, Closure $next);

    /**
     * Helper to standardize how suggestions are pushed to the model.
     */
    protected function addSuggestion(SentinelLog $log, Suggestion $suggestion): void
    {
        // Automatically attach the name of the Heuristic class for debugging
        $suggestion->setHandler(static::class);

        $suggestions = $log->suggestions ?: [];
        $suggestions[] = $suggestion->toArray();

        $log->suggestions = $suggestions;
    }
}
