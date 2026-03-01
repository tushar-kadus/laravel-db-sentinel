<?php

namespace Atmos\DbSentinel\Tests\Traits;

use Atmos\DbSentinel\CaptureRulebook;
use Mockery;

trait MocksCaptureRulebook
{
    /**
     * Mock the Rulebook
     */
    protected function mockCaptureRulebook(bool $returnValue = true): CaptureRulebook
    {
        $rulebook = Mockery::mock(CaptureRulebook::class);
        $rulebook->shouldReceive('shouldCapture')->andReturn($returnValue);
        return $rulebook;
    }
}
