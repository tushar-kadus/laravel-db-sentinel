<?php

namespace Atmos\DbSentinel\Tests;

use Atmos\DbSentinel\DbSentinelServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Atmos\DbSentinel\Jobs\AnalyzeQueryJob;
use Atmos\DbSentinel\Models\SentinelLog;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Config;
use Atmos\DbSentinel\CaptureRulebook;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\View;
use Atmos\DbSentinel\Tests\TestCase;
use Atmos\DbSentinel\CapturedQuery;
use Atmos\DbSentinel\DbSentinel;
use Mockery;

class DashboardTest extends TestCase
{
    #[Test]
    public function index_page_loads_successfully()
    {
        // setup tables and trigger fake query
        $this->setupTestTables();
        $sentinel = new DbSentinel($this->mockCaptureRulebook(true));
        $sentinel->listen($this->createFakeQuery());

        // Hit the index route
        $response = $this->get(route('db-sentinel.logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('db-sentinel::dashboard.index');
        $response->assertViewHasAll(['logs', 'counts']);
        
        // Ensure the counts array has the expected keys
        $counts = $response->viewData('counts');
        $this->assertArrayHasKey('all', $counts);
        $this->assertArrayHasKey('pending', $counts);
        $this->assertArrayHasKey('analyzed', $counts);
        $this->assertArrayHasKey('failed', $counts);
    }

    #[Test]
    public function show_page_loads_for_existing_log()
    {
        // setup tables and trigger fake query
        $this->setupTestTables();
        $sentinel = new DbSentinel($this->mockCaptureRulebook(true));
        $sentinel->listen($this->createFakeQuery());

        // Hit the show route
        $response = $this->get(route(
            'db-sentinel.logs.show',
            SentinelLog::first(['id'])->getKey()
        ));

        $response->assertStatus(200);
        $response->assertViewIs('db-sentinel::dashboard.show');
        $response->assertViewHas('log');
    }

    #[Test]
    public function filter_parameters_do_not_cause_errors()
    {
        // Pass a status filter to the query string
        $response = $this->get(route(
            'db-sentinel.logs.index',
            ['status' => 'failed']
        ));

        $response->assertStatus(200);
        $response->assertViewIs('db-sentinel::dashboard.index');
        $response->assertViewHasAll(['logs', 'counts']);

        // Ensure the counts array has the expected keys
        $counts = $response->viewData('counts');
        $this->assertArrayHasKey('all', $counts);
        $this->assertArrayHasKey('pending', $counts);
        $this->assertArrayHasKey('analyzed', $counts);
        $this->assertArrayHasKey('failed', $counts);
    }

    #[Test]
    public function show_page_returns_404_for_invalid_id()
    {
        $response = $this->get(route('db-sentinel.logs.show', 999999));
        $response->assertStatus(404);
    }
}
