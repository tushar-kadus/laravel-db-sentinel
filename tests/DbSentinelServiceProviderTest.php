<?php

namespace Atmos\DbSentinel\Tests;

use Atmos\DbSentinel\DbSentinelServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Atmos\DbSentinel\Tests\TestCase;
use Atmos\DbSentinel\DbSentinel;

class DbSentinelServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_singleton_correctly()
    {
        // Verify 'db-sentinel' is bound in the container
        $this->assertTrue($this->app->bound('db-sentinel'));

        // Verify it resolves to the correct class
        $instance = $this->app->make('db-sentinel');
        $this->assertInstanceOf(DbSentinel::class, $instance);
    }

    #[Test]
    public function it_merges_config_defaults()
    {
        // Verify the config key exists and has a value from default config file
        $this->assertTrue(Config::has('db-sentinel'));
        $this->assertIsBool(config('db-sentinel.enabled'));
    }

    #[Test]
    public function it_loads_routes_and_views_when_dashboard_is_enabled()
    {
        // Ensure config is set to enabled
        config(['db-sentinel.dashboard.enabled' => true]);
        config(['db-sentinel.dashboard.enabled' => true]);

        // Check if views exist
        $this->assertTrue(View::exists('db-sentinel::dashboard.index'));
        $this->assertTrue(View::exists('db-sentinel::dashboard.show'));

        // Check if routes are registered
        $this->assertTrue(Route::has('db-sentinel.logs.index'));
        $this->assertTrue(Route::has('db-sentinel.logs.show'));
    }
}
