<?php

declare(strict_types=1);

namespace App\Actions\Admin\Trip;

use App\DTOs\Admin\Trip\AdminTripListData;
use App\Queries\Builders\TripQueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Action for retrieving trips list for admin interface.
 */
final class GetTripsListAction
{
    /**
     * Execute the action to get trips list.
     *
     * @return LengthAwarePaginator<int, \App\Models\Trip>
     */
    public function execute(AdminTripListData $data): LengthAwarePaginator
    {
        return (new TripQueryBuilder)
            ->orderByDeparture()
            ->with(['bus:id,bus_code,type'])
            ->when($data->hasSearch(), fn ($query): \App\Queries\Builders\TripQueryBuilder => $query->search($data->search, ['origin', 'destination']))
            ->when($data->hasActive(), fn ($query): \App\Queries\Builders\TripQueryBuilder => $query->active($data->active))
            ->when($data->hasBusId(), fn ($query) => $query->where('bus_id', $data->busId))
            ->paginate(
                perPage: $data->perPage,
                columns: ['id', 'origin', 'destination', 'departure_time', 'arrival_time', 'price', 'bus_id', 'is_active', 'created_at'],
                pageName: 'page',
                page: $data->page
            );
    }
}
