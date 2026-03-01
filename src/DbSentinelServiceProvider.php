<?php

namespace Atmos\DbSentinel;

use Atmos\DbSentinel\Console\Commands\PruneLogsCommand;
use Atmos\DbSentinel\Http\Middleware\AuthorizeSentinel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Console\Scheduling\Schedule;
use Atmos\DbSentinel\Facades\DbSentinel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class DbSentinelServiceProvider extends ServiceProvider
{
    public $singletons = [
        'db-sentinel' => \Atmos\DbSentinel\DbSentinel::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge the package configuration to ensure defaults are available
        $this->mergeConfigFrom(__DIR__.'/../config/db-sentinel.php', 'db-sentinel');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // If the package is globally disabled, stop here.
        if (! config('db-sentinel.enabled', true)) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->configureScheduling();
            // Setup Publishing (only for CLI)
            $this->offerPublishing();
        }

        // Load Package Resources
        $this->registerResources();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerQueryListener();
    }

    /**
     * Bind the engine to Laravel's database query events.
     */
    private function registerQueryListener(): void
    {
        DB::listen(function (QueryExecuted $query) {
            try {
                DbSentinel::listen($query);
            } catch (Exception $e) {
                // Prevent DB Sentinel from crashing the main app if it fails
                if (config('app.debug')) {
                    Log::error('DB Sentinel Error: ' . $e->getMessage());
                }
            }
        });
    }

    /**
     * Load routes, views, and other package essentials.
     */
    private function registerResources(): void
    {
        // Only load the dashboard if it is enabled
        if (config('db-sentinel.dashboard.enabled', true)) {
            // Register the middleware with an alias
            $this->app['router']->aliasMiddleware('sentinel.auth', AuthorizeSentinel::class);
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'db-sentinel');
        }
    }

    /**
     * Register the package's artisan commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            PruneLogsCommand::class,
        ]);
    }

    /**
     * Configure the automatic scheduling for pruning.
     */
    protected function configureScheduling(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            if (config('db-sentinel.scheduling.enabled', true)) {
                $schedule->command('db-sentinel:prune')
                    ->daily()
                    ->onOneServer() // Prevents duplicates on multi-server setups
                    ->withoutOverlapping(); // Prevents a second run if the first is still going
            }
        });
    }

    /**
     * Setup the resource publishing for the artisan vendor:publish command.
     */
    private function offerPublishing(): void
    {
        // Publish Config
        $this->publishes([
            __DIR__.'/../config/db-sentinel.php' => config_path('db-sentinel.php'),
        ], 'db-sentinel-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/db-sentinel'),
        ], 'db-sentinel-views');
    }
}
