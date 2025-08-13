<?php

declare(strict_types=1);

namespace App\Actions\User\Reservation;

use App\DTOs\User\Reservation\ReservationData;
use App\Enums\ReservationStatus;
use App\Exceptions\InsufficientSeatsException;
use App\Models\Reservation;
use App\Models\Trip;
use App\Queries\Builders\ReservationQueryBuilder;
use App\Queries\Builders\TripQueryBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Action to create a new user reservation with proper seat locking.
 */
final class CreateReservationAction
{
    /**
     * Execute the action to create a new user reservation.
     *
     * @throws ModelNotFoundException When trip doesn't exist or is not active/upcoming
     * @throws InsufficientSeatsException When not enough seats available
     */
    public function execute(ReservationData $data, int $userId): Reservation
    {
        return DB::transaction(function () use ($data, $userId): Reservation {
            // Lock the trip row to prevent concurrent reservations
            $trip = (new TripQueryBuilder)
                ->with('bus')
                ->active()
                ->lockForUpdate() // This prevents race conditions
                ->findOrFail($data->tripId);

            // Validate that the trip departure is in the future
            if ($trip->departure_time <= now()) {
                throw new ModelNotFoundException('This trip is no longer available for reservation.');
            }

            // Validate seat availability with proper locking
            $this->validateSeatAvailability($trip, $data->seatsCount);

            // Create the reservation
            $reservation = Reservation::create($data->toArray($userId));

            return $reservation->load(['user', 'trip.bus', 'trip.originCity', 'trip.destinationCity']);
        });
    }

    /**
     * Validate that there are enough seats available for the trip.
     * This method is called within a database transaction with row locking.
     */
    private function validateSeatAvailability(Trip $trip, int $requestedSeats): void
    {
        // Get all confirmed reservations for this trip (within the same transaction)
        $reservedSeats = (new ReservationQueryBuilder(['seats_count']))
            ->forTrip($trip->id)
            ->confirmed()
            ->get()
            ->sum('seats_count');

        $availableSeats = $trip->bus->capacity - $reservedSeats;

        if ($requestedSeats > $availableSeats) {
            throw new InsufficientSeatsException($requestedSeats, $availableSeats);
        }
    }
}
