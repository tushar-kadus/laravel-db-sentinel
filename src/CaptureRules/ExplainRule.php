<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;
use Illuminate\Support\Str;

class ExplainRule extends CaptureRule
{
    /**
     * Determine if the query is an EXPLAIN statement.
     * * @param CapturedQuery $capturedQuery
     * @return bool Returns false if the query is an EXPLAIN statement.
     */
    protected function passes(CapturedQuery $capturedQuery): bool
    {
        // Normalize the string to lowercase and trim whitespace 
        // to ensure we catch 'EXPLAIN ...' or 'explain ...'
        $sql = Str::lower(trim($capturedQuery->sql));

        if (Str::startsWith($sql, 'explain')) {
            return false;
        }

        return true;
    }
}
