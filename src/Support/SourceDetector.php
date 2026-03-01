<?php

namespace Atmos\DbSentinel\Support;

use Illuminate\Support\Str;

/**
 * Class SourceDetector
 * * Responsible for identifying the exact line of application code that triggered
 * a specific code, while intelligently ignoring framework and package internals.
 */
final class SourceDetector
{
    /**
     * Detect the application-level source of the database query.
     *
     * @return array
     */
    public static function detect(): array
    {
        /**
         * Limit the backtrace to 20 frames for performance.
         * IGNORE_ARGS prevents high memory usage by not capturing function arguments.
         */
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        foreach ($stack as $index => $frame) {
            // We need a file and line to provide a useful report
            if (!isset($frame['file']) || !isset($frame['line'])) {
                continue;
            }

            // Standardize directory separators for cross-OS compatibility (Windows vs Linux)
            $file = str_replace('\\', '/', $frame['file']);

            /**
             * Skip frames that belong to:
             *      /vendor/
             *      This package itself (/atmos/laravel-db-sentinel/)
             */
            if (static::shouldIgnore($file)) {
                continue;
            }

            /**
             * The "next" frame (index + 1) in the stack contains the class and 
             * function/method that was executing at the time this line was hit.
             */
            $nextFrame = $stack[$index + 1] ?? null;

            $filePath = static::getRelativePath($file);

            return [
                'file' => $filePath,
                'line' => $frame['line'],
                'class' => $nextFrame['class'] ?? null,
                'method' => $nextFrame['function'] ?? null,
                'location' => static::formatLocation($filePath, $frame['line'], $nextFrame),
            ];
        }

        return [
            'file' => 'unknown',
            'line' => 0,
            'class' => null,
            'method' => null,
            'location' => 'unknown',
        ];
    }

    /**
     * Determine if a file path is part of the framework or package internals.
     *
     * @param string $file
     * @return bool
     */
    protected static function shouldIgnore(string $file): bool
    {
        return Str::contains($file, [
            '/vendor/',
            '/atmos/laravel-db-sentinel/',
        ]);
    }

    /**
     * Converts an absolute system path into a clean application-relative path.
     *
     * @param string $file
     * @return string
     */
    protected static function getRelativePath(string $file): string
    {
        // Standardize the base path to ensure the replacement works across OS types
        $basePath = str_replace('\\', '/', base_path() . '/');

        return (string) Str::replaceFirst($basePath, '', $file);
    }

    /**
     * Formats the source into a readable "Class@method (file:line)" string.
     *
     * @param string $file
     * @param int $line
     * @param array|null $nextFrame
     * @return string
     */
    protected static function formatLocation(string $filePath, int $line, ?array $nextFrame): string
    {
        if ($nextFrame && isset($nextFrame['class'])) {
            // Use class_basename to avoid long namespaces in the summary
            $className = class_basename($nextFrame['class']);
            return "{$className}@{$nextFrame['function']} ({$filePath}:{$line})";
        }

        return "{$filePath}:{$line}";
    }
}
