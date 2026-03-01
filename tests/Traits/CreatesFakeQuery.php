<?php

namespace Atmos\DbSentinel\Tests\Traits;

use Illuminate\Database\Events\QueryExecuted;

trait CreatesFakeQuery
{
    /**
     * Create a fake QueryExecuted event
     */
    protected function createFakeQuery(): QueryExecuted
    {
        return new QueryExecuted(
            'select * from sentinel_users where id = ?',
            [1],
            10.5,
            $this->app['db']->connection()
        );
    }
}
