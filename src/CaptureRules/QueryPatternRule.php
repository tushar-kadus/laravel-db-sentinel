<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;

class QueryPatternRule extends CaptureRule
{
    protected function passes(CapturedQuery $capturedQuery): bool
    {
        $ignoredPatterns = config('db-sentinel.ignore_patterns', []);

        if (empty($ignoredPatterns)) return true;

        $sql = trim($capturedQuery->sql);

        foreach ($ignoredPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return false;
            }
        }

        return true;
    }
}
