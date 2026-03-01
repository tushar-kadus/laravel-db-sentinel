<?php

namespace Atmos\DbSentinel\Tests;

use Atmos\DbSentinel\Tests\Traits\MocksCaptureRulebook;
use Atmos\DbSentinel\Tests\Traits\CreatesTestTables;
use Atmos\DbSentinel\Tests\Traits\CreatesFakeQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Atmos\DbSentinel\DbSentinelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase, CreatesTestTables, CreatesFakeQuery, MocksCaptureRulebook;

    /**
     * Load the Service Provider
     */
    protected function getPackageProviders($app)
    {
        return [
            DbSentinelServiceProvider::class,
        ];
    }
}
