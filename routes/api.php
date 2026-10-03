<?php

use App\Http\Controllers\UsageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MerchantDashboardController;

Route::post('/usage', [UsageController::class, 'store'])
    ->middleware('throttle:usage')
    ->name('usage.store');


Route::get(
    '/merchants/{merchant}/dashboard',
    [MerchantDashboardController::class, 'show']
);