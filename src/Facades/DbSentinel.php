<?php

namespace Atmos\DbSentinel\Facades;

use Illuminate\Support\Facades\Facade;

class DbSentinel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db-sentinel';
    }
}
