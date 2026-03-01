<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
if (! class_exists('CreateSentinelLogsTable')) {
class CreateSentinelLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $connection = config('db-sentinel.database.connection');
        $tableName = config('db-sentinel.database.table_name');

        Schema::connection($connection)->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            // Query Fingerprinting
            // This allows us to group queries even if their bindings change.
            $table->string('hash', 32)->index()->comment('MD5 hash of the normalized SQL');

            // SQL Query relared data
            $table->string('connection')->index();
            $table->text('sql');
            $table->json('bindings')->nullable();
            $table->decimal('execution_time', 10, 4)->index();

            // Request Context
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->json('caller')->nullable();

            // Analysis
            $table->json('explanation')->nullable();
            $table->json('suggestions')->nullable();
            
            $table->string('status', 20)->default('pending')->index();

            $table->timestamps();

            // Performance Index
            $table->index(['status', 'execution_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $connection = config('db-sentinel.database.connection');
        $tableName = config('db-sentinel.database.table_name');

        Schema::connection($connection)->dropIfExists($tableName);
    }
}
}