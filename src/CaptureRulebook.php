<?php

namespace Atmos\DbSentinel;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pipeline\Pipeline;

class CaptureRulebook
{
    /**
     * Send the event through the pipeline of rules.
     */
    public function shouldCapture(CapturedQuery $capturedQuery): bool
    {
        $rules = config('db-sentinel.rules', []);

        // We use the capturedQuery as the "passable" object.
        // If the pipeline completes and returns true, it passed!
        // If any pipe returns null/false, the result will be falsy.
        $result = app(Pipeline::class)
            ->send($capturedQuery)
            ->through($rules)
            ->then(function ($capturedQuery) {
                return ['status' => true];
            });

        return $result['status'];
    }
}
