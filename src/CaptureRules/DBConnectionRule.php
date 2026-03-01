<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;

class DBConnectionRule extends CaptureRule
{
    protected function passes(CapturedQuery $capturedQuery): bool
    {
    	$allowed = config('db-sentinel.allowed_connections', []);

        // If 'allowed' is set, the connection must be in it.
        if (!empty($allowed) && !in_array($capturedQuery->connection, $allowed)) {
            return false;
        }

        $ignored = config('db-sentinel.ignored_connections', []);

        // If 'ignored' is set, the connection must NOT be in it.
        if (!empty($ignored) && in_array($capturedQuery->connection, $ignored)) {
            return false;
        }

        return true;
    }
}
