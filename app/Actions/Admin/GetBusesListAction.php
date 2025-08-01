<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\AdminBusListData;
use App\Queries\Builders\BusQueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Action for retrieving buses list for admin interface.
 */
final class GetBusesListAction
{
    /**
     * Execute the action to get buses list.
     *
     * @return LengthAwarePaginator<int, \App\Models\Bus>
     */
    public function execute(AdminBusListData $data): LengthAwarePaginator
    {
        return (new BusQueryBuilder)
            ->orderByCreated()
            ->when($data->hasSearch(), fn ($query): \App\Queries\Builders\BusQueryBuilder => $query->search($data->search, ['bus_code']))
            ->when($data->hasType(), fn ($query): \App\Queries\Builders\BusQueryBuilder => $query->ofType($data->type))
            ->when($data->hasActive(), fn ($query): \App\Queries\Builders\BusQueryBuilder => $query->active($data->active))
            ->paginate(
                perPage: $data->perPage,
                columns: ['id', 'bus_code', 'capacity', 'type', 'is_active', 'created_at'],
                pageName: 'page',
                page: $data->page
            );
    }
}
