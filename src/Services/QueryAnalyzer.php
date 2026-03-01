<?php

namespace Atmos\DbSentinel\Services;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Pipeline\Pipeline;

class QueryAnalyzer
{
    public function analyze(SentinelLog $log)
    {
        $heuristics = config('db-sentinel.heuristics', []);

        return app(Pipeline::class)
            ->send($log)
            ->through($heuristics)
            ->then(function ($log) {
                return $log;
            });
    }
}
