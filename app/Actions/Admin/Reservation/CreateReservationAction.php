<?php

declare(strict_types=1);

namespace App\Actions\Admin\Reservation;

use App\DTOs\Admin\Reservation\ReservationData;
use App\Exceptions\InsufficientSeatsException;
use App\Models\Reservation;
use App\Models\Trip;
use App\Queries\Builders\ReservationQueryBuilder;
use App\Queries\Builders\TripQueryBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Action to create a new reservation with proper business rule validation.
 */
final class CreateReservationAction
{
    /**
     * Execute the action to create a new reservation.
     *
     * @throws ModelNotFoundException When trip doesn't exist or is not active/upcoming
     * @throws InsufficientSeatsException When not enough seats available
     */
    public function execute(ReservationData $data): Reservation
    {
        return DB::transaction(function () use ($data): Reservation {
            $trip = (new TripQueryBuilder())
                ->with('bus')
                ->active()
                ->findOrFail($data->tripId);
            
            $this->validateSeatAvailability($trip, $data->seatsCount);

            // Create the reservation (code generation handled by model)
            $reservation = Reservation::create($data->toArray());

            return $reservation->load(['user', 'trip.bus']);
        });
    }

    /**
     * Validate that there are enough seats available for the trip.
     */
    private function validateSeatAvailability(Trip $trip, int $requestedSeats): void
    {
        $reservedSeats = (new ReservationQueryBuilder(['seats_count']))
            ->forTrip($trip->id)
            ->confirmed()
            ->get();

        $reservedSeats = $reservedSeats->sum('seats_count');
        
        $availableSeats = $trip->bus->capacity - $reservedSeats;
        
        if ($requestedSeats > $availableSeats) {
            throw new InsufficientSeatsException($requestedSeats, $availableSeats);
        }
    }
}