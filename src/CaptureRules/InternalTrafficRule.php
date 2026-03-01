<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;
use Illuminate\Support\Str;

class InternalTrafficRule extends CaptureRule
{
    protected function passes(CapturedQuery $capturedQuery): bool
    {
        $sentinelTable = config('db-sentinel.database.table', 'sentinel_logs');

        if (Str::contains($capturedQuery->sql, $sentinelTable)) {
            return false;
        }

        return true;
    }
}
