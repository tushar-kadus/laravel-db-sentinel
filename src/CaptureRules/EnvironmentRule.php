<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;

class EnvironmentRule extends CaptureRule
{
    protected function passes(CapturedQuery $capturedQuery): bool
    {
    	// Check if the current environment is in the allowed environments list
        $allowedEnvs = config('db-sentinel.environments', ['local', 'staging']);

        if (!empty($allowedEnvs) && !app()->environment($allowedEnvs)) {
            return false;
        }

        return true;
    }
}
