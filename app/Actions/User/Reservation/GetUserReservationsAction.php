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
            ])
            ->forUser($userId);

        // Apply filters
        if ($data->search !== null) {
            $query->search($data->search);
        }

        if ($data->status !== null) {
            $query->withStatus($data->status);
        }

        if ($data->startDate !== null && $data->endDate !== null) {
            $query->dateRange($data->startDate, $data->endDate);
        }

        if ($data->upcomingOnly) {
            $query->upcoming();
        }

        // Apply sorting
        if ($data->sortBy === 'departure_time') {
            $query->orderByDeparture($data->sortDirection);
        } else {
            $query->orderByCreated($data->sortDirection);
        }

        return $query->paginate($data->perPage);
    }
}
