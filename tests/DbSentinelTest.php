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

class DbSentinelTest extends TestCase
{
    #[Test]
    public function it_captures_query_when_rulebook_allows_it()
    {
        Queue::fake();

        // setup tables and trigger fake query
        $this->setupTestTables();
        $sentinel = new DbSentinel($this->mockCaptureRulebook(true));
        $sentinel->listen($this->createFakeQuery());

        // Assert: Database has the record
        $this->assertDatabaseHas('sentinel_logs', [
            'sql' => 'select * from sentinel_users where id = ?',
            'status' => 'pending'
        ]);

        // Assert: Background Job was dispatched
        Queue::assertPushed(AnalyzeQueryJob::class);
    }

    #[Test]
    public function it_ignores_query_when_rulebook_denies_it()
    {
        Queue::fake();

        // setup tables and trigger fake query
        $this->setupTestTables();
        $sentinel = new DbSentinel($this->mockCaptureRulebook(false));
        $sentinel->listen($this->createFakeQuery());

        // Assert: Database is empty (no record saved)
        $this->assertDatabaseCount('sentinel_logs', 0);

        // Assert: No Job was dispatched
        Queue::assertNothingPushed();
    }
}
