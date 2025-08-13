<?php

declare(strict_types=1);

namespace App\Actions\User\Reservation;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Action to cancel a user reservation.
 */
final class CancelReservationAction
{
    /**
     * Execute the action to cancel a user reservation.
     *
     * @throws ModelNotFoundException When reservation doesn't exist or doesn't belong to user
     */
    public function execute(Reservation $reservation, int $userId): Reservation
    {
        return DB::transaction(function () use ($reservation, $userId): Reservation {
            // Verify the reservation belongs to the authenticated user
            if ($reservation->user_id !== $userId) {
                throw new ModelNotFoundException('Reservation not found.');
            }

            // Check if reservation can be cancelled
            if ($reservation->status === ReservationStatus::CANCELLED) {
                throw new \InvalidArgumentException('Reservation is already cancelled.');
            }

            // Check if trip has already departed
            if ($reservation->trip->departure_time <= now()) {
                throw new \InvalidArgumentException('Cannot cancel reservation for a trip that has already departed.');
            }

            // Update reservation status
            $reservation->update([
                'status' => ReservationStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $reservation->load(['user', 'trip.bus', 'trip.originCity', 'trip.destinationCity']);
        });
    }
}
