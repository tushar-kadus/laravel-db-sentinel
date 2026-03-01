<?php

namespace Atmos\DbSentinel\CaptureRules;

use Atmos\DbSentinel\CapturedQuery;

class VendorQueryRule extends CaptureRule
{
    protected function passes(CapturedQuery $capturedQuery): bool
    {
        // If caller is empty then it is query triggerred from vendor
        return !empty($capturedQuery->caller);
    }
}
