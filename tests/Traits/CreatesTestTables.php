<?php

namespace Atmos\DbSentinel\Tests\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesTestTables
{
    /**
     * Define and create dummy tables needed for package testing.
     */
    protected function setupTestTables(): void
    {
        if (!Schema::hasTable('sentinel_users')) {
            Schema::create('sentinel_users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->timestamps();
            });
        }
    }
}
