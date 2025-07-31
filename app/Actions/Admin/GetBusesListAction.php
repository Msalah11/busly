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
        $query = BusQueryBuilder::make()
            ->orderByCreated();

        if ($data->hasSearch()) {
            $query->search($data->search, ['bus_code']);
        }

        if ($data->hasType()) {
            $query->ofType($data->type);
        }

        if ($data->hasActive()) {
            $query->active($data->active);
        }

        return $query->paginate(
            perPage: $data->perPage,
            columns: ['id', 'bus_code', 'capacity', 'type', 'is_active', 'created_at'],
            pageName: 'page',
            page: $data->page
        )->withQueryString();
    }
} 