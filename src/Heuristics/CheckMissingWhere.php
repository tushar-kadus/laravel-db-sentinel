<?php

namespace Atmos\DbSentinel\Heuristics;

use Atmos\DbSentinel\Models\SentinelLog;
use Closure;

class CheckMissingWhere extends Heuristic
{
    public function handle(SentinelLog $log, Closure $next)
    {
        $sql = strtolower($log->sql);

        $isWrite = (strpos($sql, 'update') === 0) || (strpos($sql, 'delete') === 0);

        if ($isWrite) {
            $explanation = is_array($log->explanation) ? $log->explanation : [];
            $usesWhere = false;

            foreach ($explanation as $node) {
                $extra = $node['Extra'] ?? $node['extra'] ?? '';
                
                // If 'Using where' is present, the engine is filtering rows
                if (stripos($extra, 'using where') !== false) {
                    $usesWhere = true;
                    break;
                }
            }

            if (!$usesWhere) {
                $this->addSuggestion($log, 
                    Suggestion::make('UNFILTERED OPERATION', 'This query appears to be modifying all rows. No filtering (WHERE) was detected by the execution engine.')
                        ->critical()
                );
            }
        }

        return $next($log);
    }
}
