<?php

use Atmos\DbSentinel\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('db-sentinel.dashboard.path'))
    ->as('db-sentinel.')
    ->middleware(array_merge(
        config('db-sentinel.dashboard.middleware', []),
        ['sentinel.auth']
    ))
    ->group(function () {
        Route::resource('logs', DashboardController::class)->only(['index', 'show']);
    });
