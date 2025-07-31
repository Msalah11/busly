<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function (): void {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User management resource routes
    Route::resource('users', UserController::class)->except(['show']);

    // Bus management resource routes
    Route::resource('buses', BusController::class)->except(['show']);

    // Trip management resource routes
    Route::resource('trips', TripController::class)->except(['show']);
});
