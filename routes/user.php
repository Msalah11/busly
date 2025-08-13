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
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
});
