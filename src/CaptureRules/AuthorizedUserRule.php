<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;

class AuthorizedUserRule extends CaptureRule
{
    protected function passes(CapturedQuery $capturedQuery): bool
    {
        // Get allowed user ids
        $allowedIds = config('db-sentinel.security.user_ids', []);

        // If the list is empty, we assume all users are allowed.
        if (empty($allowedIds)) return true;

        // check if the current auth user is in the allowed list.
        if (auth()->check() && !in_array(auth()->id(), $allowedIds)) {
            return false;
        }

        return true;
    }
}
