<?php

namespace Atmos\DbSentinel\Support;

use Atmos\DbSentinel\Support\SqlTokenizer;
use Atmos\DbSentinel\Support\SqlCleaner;

final class SqlWildcardInspector
{
    /**
     * Standard SQL Aggregate/JSON functions that use parenthesis.
     */
    private static $ignoredFunctions = [
        // True Aggregates
        'COUNT', 'SUM', 'AVG', 'MIN', 'MAX', 'MEDIAN',
        // JSON/Path Functions (Common in MySQL/Postgres/SQLite)
        'JSON_EXTRACT', 'JSON_QUERY', 'JSON_VALUE', 'JSON_GET'
    ];

    /**
     * Detect if a query selects all columns.
     * * @param string $sql
     * @return bool
     */
    public static function hasSelectAll(string $sql): bool
    {
        // Step 1: Remove comments using the Support class
        $cleanSql = SqlCleaner::stripComments($sql);

        // Step 2: Break into tokens using the Support class
        $tokens = SqlTokenizer::tokenize($cleanSql);

        // Step 3: Iterate through tokens to find the wildcard
        foreach ($tokens as $index => $token) {
            // Check for standalone '*'
            if ($token === '*') {
                if (self::isActualWildcard($tokens, $index)) {
                    return true;
                }
            }

            // Check for namespaced wildcards: table.* or [table].* or db.table.* etc.
            // Regex: Start with opt. quote, word, end opt. quote, dot, asterisk
            if (preg_match('/^[\[\"\'\`]?\w+[\]\"\'\`]?\s*\.\s*\*$/i', $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates if the '*' token at a specific index is a wildcard.
     */
    private static function isActualWildcard(array $tokens, int $index): bool
    {
        $prev = self::getNeighbor($tokens, $index, -1);

        // Handle Aggregates
        // If previous token is '(', check if token before that is aggregate function
        if ($prev === '(') {
            $funcName = strtoupper(self::getNeighbor($tokens, $index, -2));
            if (in_array($funcName, self::$ignoredFunctions)) {
                return false; // It is COUNT(*), not SELECT *
            }
        }

        $next = self::getNeighbor($tokens, $index, 1);

        // Handle Math: Check if * is between two operands (e.g., price * quantity)
        // If the asterisk has an identifier/number on BOTH sides, it is multiplication
        if (self::isOperand($prev) && self::isOperand($next)) {
            return false; // It is math
        }

        // Handle JSON: If preceded by -> or ->>, it's a JSON path selection
        if ($prev === '->' || $prev === '->>') {
            return false;
        }

        return true;
    }

    /**
     * Safely retrieves a neighbor token relative to the current index.
     */
    private static function getNeighbor(array $tokens, int $index, int $offset): string
    {
        $target = $index + $offset;
        return isset($tokens[$target]) ? trim($tokens[$target]) : '';
    }

    /**
     * Determines if a token is a valid operand (column, table, number, or quoted string).
     */
    private static function isOperand(string $token): bool
    {
        if ($token === '') return false;

        // Matches words, numbers, and identifiers wrapped in "", ``, '', or []
        // \w: word characters, \d: digits, \s: whitespace
        return (bool) preg_match('/^[\w\d\s\[\]\"\'\`]+$/', $token);
    }
}
