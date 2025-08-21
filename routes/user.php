<?php

declare(strict_types=1);

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ReservationController;
use App\Http\Controllers\User\TripController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('user.')->group(function (): void {
    // User Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Trip Browsing
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');

    // User Reservations
    Route::resource('reservations', ReservationController::class)->except(['edit', 'update']);
});
