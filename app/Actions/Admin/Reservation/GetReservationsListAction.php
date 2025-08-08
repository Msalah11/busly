<?php

declare(strict_types=1);

namespace App\Actions\Admin\Reservation;

use App\DTOs\Admin\Reservation\AdminReservationListData;
use App\Queries\Builders\ReservationQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Action to get a paginated list of reservations with filtering options.
 */
class GetReservationsListAction
{
    /**
     * Execute the action.
     *
     * @return LengthAwarePaginator<int, \App\Models\Reservation>
     */
    public function execute(AdminReservationListData $data): LengthAwarePaginator
    {
        $query = (new ReservationQueryBuilder)
            ->with(['user:id,name,email', 'trip:id,origin_city_id,destination_city_id,departure_time,price,bus_id', 'trip.bus:id,bus_code', 'trip.originCity:id,name,code', 'trip.destinationCity:id,name,code'])
            ->when($data->hasSearch(), fn ($query) => $query->search($data->search, ['reservation_code']))
            ->when($data->hasStatus(), fn ($query) => $query->withStatus($data->status))
            ->when($data->hasUser(), fn ($query) => $query->forUser($data->userId))
            ->when($data->hasTrip(), fn ($query) => $query->forTrip($data->tripId))
            ->when($data->hasDateRange(), fn ($query) => $query->reservedBetween($data->startDate, $data->endDate))
            ->orderByCreated();

        return $query->paginate(15);
    }
}