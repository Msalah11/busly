<?php

declare(strict_types=1);

namespace App\Actions\User\Trip;

use App\DTOs\User\Trip\TripSearchData;
use App\Queries\Builders\TripQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Action to search available trips for users.
 */
final class SearchTripsAction
{
    /**
     * Execute the action to search trips with filters.
     *
     * @return LengthAwarePaginator<\App\Models\Trip>
     */
    public function execute(TripSearchData $data): LengthAwarePaginator
    {
        $query = (new TripQueryBuilder)
            ->with([
                'bus:id,bus_code,capacity,type',
                'originCity:id,name,code',
                'destinationCity:id,name,code',
                'reservations' => function ($query): void {
                    $query->where('status', '!=', \App\Enums\ReservationStatus::CANCELLED)
                        ->select('trip_id', 'seats_count');
                },
            ])
            ->active()
            ->upcoming(); // Only show future trips

        // Apply search filters
        if ($data->originCityId !== null) {
            $query->fromCity($data->originCityId);
        }

        if ($data->destinationCityId !== null) {
            $query->toCity($data->destinationCityId);
        }

        if ($data->departureDate !== null) {
            $query->onDate($data->departureDate);
        }

        if ($data->maxPrice !== null) {
            $query->maxPrice($data->maxPrice);
        }

        // Apply sorting
        if ($data->sortBy === 'price') {
            $query->orderByPrice($data->sortDirection);
        } else {
            $query->orderByDeparture($data->sortDirection);
        }

        $trips = $query->paginate($data->perPage);

        // Calculate available seats for each trip
        $trips->getCollection()->transform(function ($trip): \App\Models\Trip {
            $trip->available_seats = $trip->getAvailableSeatsAttribute();
            
            // Filter by minimum seats if specified
            return $trip;
        });

        // If min_seats filter is specified, filter the collection
        if ($data->minSeats !== null) {
            $trips->getCollection()->filter(function ($trip) use ($data): bool {
                return $trip->available_seats >= $data->minSeats;
            });
        }

        return $trips;
    }
}
