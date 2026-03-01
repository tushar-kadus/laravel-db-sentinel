<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;
use Closure;

abstract class CaptureRule
{
    /**
     * Handle the logic for ignoring or capturing the query.
     */
    protected abstract function passes(CapturedQuery $capturedQuery): bool;

    /**
     * Handle the rule logic.
     */
    public function handle(CapturedQuery $capturedQuery, Closure $next)
    {
        if (!$this->passes($capturedQuery)) {
            return [
                'status' => false,
                'rule'   => static::class
            ];
        }

        return $next($capturedQuery);
    }
}
