<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master Switch
    |--------------------------------------------------------------------------
    |
    | Completely enable or disable the Sentinel query monitoring. When disabled,
    | the event listener will not record any queries, and the dashboard 
    | will be inaccessible.
    |
    */

    'enabled' => env('DB_SENTINEL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database Storage & Retention
    |--------------------------------------------------------------------------
    |
    | 'connection': The DB connection where logs are stored (e.g., 'mysql').
    | 'table': The name of the table created by the migration.
    | 'prune_after_days': Auto-delete logs older than this to save space.
    | 'max_sql_length': Truncate extremely long queries to prevent DB bloat.
    |
    */

    'database' => [
        'connection' => env('DB_SENTINEL_CONNECTION', env('DB_CONNECTION', 'mysql')),
        'table_name' => env('DB_SENTINEL_LOGS_TABLE', 'sentinel_logs'),
        'prune_after_days' => env('DB_SENTINEL_PRUNE_DAYS', 30),
        'max_sql_length' => env('DB_SENTINEL_MAX_SQL', 5000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance & Queuing
    |--------------------------------------------------------------------------
    |
    | 'threshold': Log any query slower than this (in milliseconds).
    | 'queue_name': The queue pipe to use for background analysis jobs.
    |
    */

    'threshold' => env('DB_SENTINEL_THRESHOLD', 500),
    'queue_name' => env('DB_SENTINEL_QUEUE_NAME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Monitoring Scope
    |--------------------------------------------------------------------------
    |
    | 'allowed_connections': Only monitor these specific database connections.
    | 'ignored_connections': Connections that should never be tracked.
    |
    */

    'allowed_connections' => array_filter(explode(',', env('DB_SENTINEL_ALLOWED_CONS', 'mysql,mariadb,pgsql,sqlsrv'))),
    'ignored_connections' => array_filter(explode(',', env('DB_SENTINEL_IGNORED_CONS', 'sqlite,testing'))),

    /*
    |--------------------------------------------------------------------------
    | Ignore Patterns (Regex)
    |--------------------------------------------------------------------------
    |
    | RegEx patterns of queries that should be ignored (e.g., sessions, 
    | migrations, or telescope entries).
    |
    */

    'ignore_patterns' => [
        // Ignore migrations
        '/^\s*insert into [`"]migrations[`"]/i',

        // Ignore system tables
        '/^\s*select .* from [`"]telescope_entries[`"]/i',
        '/^\s*select .* from [`"]sessions[`"]/i',
        '/^\s*update [`"]sessions[`"] set/i',
        '/^\s*delete from [`"]sessions[`"]/i',
        '/^\s*insert into [`"]sessions[`"]/i',
        '/^\s*insert into [`"]notifications[`"]/i',

        // Ignore Pulse record inserts and system checks
        '/^\s*insert into [`"]pulse_\w+[`"]/i',
        '/^\s*select .* from [`"]pulse_\w+[`"]/i',

        // Ignore Queue and job management
        '/[`"](jobs|failed_jobs|job_batches)[`"]/i',

        // Ignore Database Cache operations
        '/^\s*select .* from [`"]cache[`"]/i',
        '/^\s*insert into [`"]cache[`"]/i',
        '/^\s*update [`"]cache[`"] set/i',
        '/^\s*delete from [`"]cache[`"]/i',

        // Ignore Sanctum / Passport token lookups
        '/^\s*select .* from [`"]personal_access_tokens[`"]/i',
        '/^\s*update [`"]personal_access_tokens[`"]/i',

        // Ignore foreign key check toggles
        '/^\s*SET\s+FOREIGN_KEY_CHECKS\s*=/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard & Security
    |--------------------------------------------------------------------------
    |
    | 'path': The URL slug used to access the UI.
    | 'middleware': The middleware stack for dashboard routes.
    | 'security': Multiple layers of access control for the dashboard.
    |
    */

    'dashboard' => [
        'enabled' => env('DB_SENTINEL_DASHBOARD', true),
        'path' => env('DB_SENTINEL_PATH', 'db-sentinel'),
        'middleware' => ['web', 'auth'],

        'security' => [
            // Numeric IDs of users allowed to see the dashboard: '1,5,10'
            'authorized_user_ids' => array_filter(explode(',', env('DB_SENTINEL_USER_IDS', ''))),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Pipeline
    |--------------------------------------------------------------------------
    |
    | These classes represent the rules used to analyze SQL queries. You can
    | add your own custom pipes here to enforce specific standards.
    |
    */

    'heuristics' => [
        \Atmos\DbSentinel\Heuristics\CheckSelectAll::class,
        \Atmos\DbSentinel\Heuristics\CheckFullTableScan::class,
        \Atmos\DbSentinel\Heuristics\CheckMissingWhere::class,
        \Atmos\DbSentinel\Heuristics\IdentifyExecutionAnomalies::class,
        \Atmos\DbSentinel\Heuristics\DetectUnusedIndexes::class,
        \Atmos\DbSentinel\Heuristics\IdentifyCartesianProducts::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Capture Rulebook (The Pipeline)
    |--------------------------------------------------------------------------
    |
    | Here you can define the list of "Rules" (Pipes) that every query must 
    | pass through before being recorded. 
    |
    | If any rule returns 'false', the query is ignored. If all rules return 
    | '$next($event)', the query is captured and analyzed.
    |
    | You can remove any rule from here or add your own custom rules here as
    | needed.
    | Every rule must implement the Atmos\DbSentinel\CaptureRules\CaptureRule
    | interface.
    |
    */

    'rules' => [
        // Filter explain queries
        \Atmos\DbSentinel\CaptureRules\ExplainRule::class,

        // Filter queries based on ignored query patterns
        \Atmos\DbSentinel\CaptureRules\QueryPatternRule::class,

        // Check if monitoring is enabled for this environment.
        \Atmos\DbSentinel\CaptureRules\EnvironmentRule::class,

        // Critical: Prevents Sentinel from logging its own database writes.
        \Atmos\DbSentinel\CaptureRules\InternalTrafficRule::class,

        // Filters queries based on allowed/ignored connection names.
        \Atmos\DbSentinel\CaptureRules\DBConnectionRule::class,

        // Restricts logging to specific User IDs (useful for production debugging).
        \Atmos\DbSentinel\CaptureRules\AuthorizedUserRule::class,

        // Detects if the query originated from the /vendor directory.
        \Atmos\DbSentinel\CaptureRules\VendorQueryRule::class,

        // Example of a custom user-defined rule:
        // \App\DbSentinel\CaptureRules\OnlyHighValueCustomersRule::class,
    ],

];
