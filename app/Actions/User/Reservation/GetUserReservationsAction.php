<?php

declare(strict_types=1);

namespace App\Actions\User\Reservation;

use App\DTOs\User\Reservation\UserReservationListData;
use App\Queries\Builders\ReservationQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Action to get user reservations with filtering and pagination.
 */
final class GetUserReservationsAction
{
    /**
     * Execute the action to get filtered user reservations.
     *
     * @return LengthAwarePaginator<\App\Models\Reservation>
     */
    public function execute(UserReservationListData $data, int $userId): LengthAwarePaginator
    {
        $query = (new ReservationQueryBuilder)
            ->with([
                'trip:id,origin_city_id,destination_city_id,departure_time,arrival_time,price,bus_id',
                'trip.bus:id,bus_code,capacity',
                'trip.originCity:id,name,code',
                'trip.destinationCity:id,name,code',
            ]);

        // Apply filters
        $query->when($data->status !== null, fn($query) => $query->withStatus($data->status));
        $query->when($data->startDate !== null && $data->endDate !== null, fn($query) => $query->dateRange($data->startDate, $data->endDate));
        $query->when($data->upcomingOnly, fn($query) => $query->upcoming());

        // Apply sorting
        $query->when($data->sortBy === 'departure_time', fn($query) => $query->orderByDeparture($data->sortDirection));
        $query->when($data->sortBy !== 'departure_time', fn($query) => $query->orderByCreated($data->sortDirection));

        return $query->forUser($userId)->paginate($data->perPage);
    }
}
