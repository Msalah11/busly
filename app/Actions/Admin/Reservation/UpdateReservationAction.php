<?php

declare(strict_types=1);

namespace App\Actions\Admin\Reservation;

use App\DTOs\Admin\Reservation\ReservationData;
use App\Enums\ReservationStatus;
use App\Exceptions\InsufficientSeatsException;
use App\Models\Reservation;
use App\Models\Trip;
use App\Queries\Builders\ReservationQueryBuilder;
use App\Queries\Builders\TripQueryBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Action to update an existing reservation with optimized queries.
 */
final class UpdateReservationAction
{
    /**
     * Execute the action to update an existing reservation.
     *
     * @throws ModelNotFoundException When trip doesn't exist or is not active/upcoming
     * @throws InsufficientSeatsException When not enough seats available
     */
    public function execute(Reservation $reservation, ReservationData $data): Reservation
    {
        return DB::transaction(function () use ($reservation, $data): Reservation {
            // If changing trip or seat count, validate the new trip and availability
            if ($data->tripId !== $reservation->trip_id || $data->seatsCount !== $reservation->seats_count) {
                $trip = (new TripQueryBuilder())
                    ->with('bus')
                    ->active()
                    ->findOrFail($data->tripId);
                
                $this->validateSeatAvailability($trip, $data->seatsCount, $reservation->id);
            }

            // Handle status-specific updates
            $updateData = $this->prepareUpdateData($data, $reservation);
            
            $reservation->update($updateData);

            return $reservation->load(['user', 'trip.bus']);
        });
    }

    /**
     * Validate that there are enough seats available for the trip.
     */
    private function validateSeatAvailability(Trip $trip, int $requestedSeats, int $excludeReservationId): void
    {
        $reservedSeats = (new ReservationQueryBuilder(['seats_count']))
            ->forTrip($trip->id)
            ->confirmed()
            ->where('id', '!=', $excludeReservationId)
            ->sum('seats_count');
        
        $availableSeats = $trip->bus->capacity - $reservedSeats;
        
        if ($requestedSeats > $availableSeats) {
            throw new InsufficientSeatsException($requestedSeats, $availableSeats);
        }
    }

    /**
     * Prepare update data with status-specific logic.
     *
     * @return array<string, mixed>
     */
    private function prepareUpdateData(ReservationData $data, Reservation $reservation): array
    {
        $updateData = $data->toArray();
        
        // Handle cancellation date logic
        if ($data->status === ReservationStatus::CANCELLED && $reservation->status !== ReservationStatus::CANCELLED) {
            $updateData['cancelled_at'] = $data->cancelledAt ?? now();
        } elseif ($data->status === ReservationStatus::CONFIRMED && $reservation->status === ReservationStatus::CANCELLED) {
            $updateData['cancelled_at'] = null;
        }

        return $updateData;
    }
}