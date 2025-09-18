<?php

use Illuminate\Support\Facades\Route;
use Itzdevsatvik\PackageHealthChecker\Controllers\PackageHealthController;

Route::group([
    'prefix' => config('packagehealthchecker.dashboard.path', 'package-health'),
    'middleware' => config('packagehealthchecker.dashboard.middleware', ['web']),
], function () {
    Route::get('/', [PackageHealthController::class, 'index'])->name('package-health.dashboard');
    Route::post('/scan', [PackageHealthController::class, 'scan'])->name('package-health.scan');
});