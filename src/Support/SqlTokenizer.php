<?php

namespace Atmos\DbSentinel\Support;

final class SqlTokenizer
{
    /**
     * Breaks a SQL string into an array of meaningful tokens.
     * * @param string $sql
     * @return array
     */
    public static function tokenize(string $sql): array
    {
        // This regex defines what constitutes a single "unit" in SQL
        $regex = '~
            \s* (                   # Ignore leading whitespace, start capturing group
                [\'\"].*?[\'\"]     # 1. Match single or double quoted strings
                | \[.*?\]           # 2. Match SQL Server square brackets
                | [`].*?[`]         # 3. Match MySQL backticks
                | \w+\s*\.\s*\* # 4. Match table.*, table . *, or alias.*
                | ->>?              # 5. Match JSON operators (Now safe!)
                | [(),.*=<>!+-]     # 6. Match single punctuation/operators
                | \w+               # 7. Match standard words, aliases, or numbers
            ) \s* # Ignore trailing whitespace, end capturing group
        ~ix'; // i: Case-insensitive, x: Ignore whitespace in the regex itself for readability

        // Perform the match
        preg_match_all($regex, $sql, $matches);

        // Return only the captured groups (index 1)
        return $matches[1] ?? [];
    }
}
