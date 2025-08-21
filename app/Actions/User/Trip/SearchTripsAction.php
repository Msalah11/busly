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
     * @return LengthAwarePaginator<int, \App\Models\Trip>
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
        $query->when($data->originCityId !== null, fn ($query) => $query->fromCity($data->originCityId));
        $query->when($data->destinationCityId !== null, fn ($query) => $query->toCity($data->destinationCityId));

        $query->when($data->departureDate !== null, fn ($query) => $query->onDate($data->departureDate));
        $query->when($data->maxPrice !== null, fn ($query) => $query->maxPrice($data->maxPrice));

        // Apply sorting
        $query->when($data->sortBy === 'price', fn ($query) => $query->orderByPrice($data->sortDirection));
        $query->when($data->sortBy !== 'price', fn ($query) => $query->orderByDeparture($data->sortDirection));

        $trips = $query->paginate($data->perPage);

        // Calculate available seats for each trip
        $trips->getCollection()->transform(function ($trip): \App\Models\Trip {
            $trip->available_seats = $trip->getAvailableSeatsAttribute();

            // Filter by minimum seats if specified
            return $trip;
        });

        // If min_seats filter is specified, filter the collection
        $trips->getCollection()->filter(fn ($trip): bool => $trip->available_seats >= $data->minSeats);

        return $trips;
    }
}
