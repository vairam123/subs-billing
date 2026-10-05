<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UsageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/merchants', [MerchantController::class, 'index'])
    ->name('merchants.index');

Route::get('/merchants/create', [MerchantController::class, 'create'])
    ->name('merchants.create');

Route::post('/merchants', [MerchantController::class, 'store'])
    ->name('merchants.store');

Route::get('/merchants/{merchant}', [MerchantController::class, 'show'])
    ->name('merchants.show');

Route::get('/merchants/{merchant}/plans', [PlanController::class, 'index'])
    ->name('merchants.plans.index');

Route::get('/merchants/{merchant}/plans/create', [PlanController::class, 'create'])
    ->name('merchants.plans.create');

Route::post('/merchants/{merchant}/plans', [PlanController::class, 'store'])
    ->name('merchants.plans.store');

Route::get('/merchants/{merchant}/plans/{plan}', [PlanController::class, 'show'])
    ->name('merchants.plans.show');

Route::get('/merchants/{merchant}/plans/{plan}/edit', [PlanController::class, 'edit'])
    ->name('merchants.plans.edit');

Route::put('/merchants/{merchant}/plans/{plan}', [PlanController::class, 'update'])
    ->name('merchants.plans.update');

Route::prefix('merchants/{merchant}')
    ->name('merchants.')
    ->group(function () {

        Route::get(
            'customers',
            [CustomerController::class, 'index']
        )->name('customers.index');

        Route::get(
            'customers/create',
            [CustomerController::class, 'create']
        )->name('customers.create');

        Route::post(
            'customers',
            [CustomerController::class, 'store']
        )->name('customers.store');
    });

Route::get(
    'customers/{customer}/subscriptions/create',
    [SubscriptionController::class, 'create']
)->name('customers.subscriptions.create');

Route::post(
    'customers/{customer}/subscriptions',
    [SubscriptionController::class, 'store']
)->name('customers.subscriptions.store');

