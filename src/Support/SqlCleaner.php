<?php

namespace Atmos\DbSentinel\Support;

final class SqlCleaner
{
    /**
     * Removes SQL comments to prevent false positives in analysis.
     * * @param string $sql
     * @return string
     */
    public static function stripComments(string $sql): string
    {
        // 1. Remove multi-line comments: /* any text */
        // ! ... !s -> The 's' modifier allows the dot (.) to match newlines
        // \*.*? -> Matches '*' followed by any character (non-greedy) until '*/'
        $sql = preg_replace('!/\*.*?\*/!s', ' ', $sql);

        // 2. Remove single-line comments starting with -- or #
        // (?:--|#) -> Non-capturing group for either -- or #
        // .* -> Match everything until the end of the line
        $sql = preg_replace('/(?:--|#).*/', '', $sql);
        
        return trim($sql);
    }
}
