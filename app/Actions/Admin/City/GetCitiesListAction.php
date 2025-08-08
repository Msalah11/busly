<?php

declare(strict_types=1);

namespace App\Actions\Admin\City;

use App\DTOs\Admin\City\AdminCityListData;
use App\Queries\Builders\CityQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Action to get a paginated list of cities with filtering options.
 */
final class GetCitiesListAction
{
    /**
     * Execute the action.
     *
     * @return LengthAwarePaginator<int, \App\Models\City>
     */
    public function execute(AdminCityListData $data): LengthAwarePaginator
    {
        $query = (new CityQueryBuilder)
            ->when($data->hasSearch(), fn ($query) => $query->search($data->search))
            ->when($data->hasActiveFilter(), fn ($query) => $query->active($data->isActive))
            ->when($data->isValidSortColumn(), fn ($query) => $query->orderBy($data->sortBy, $data->sortDirection));

        return $query->paginate(15);
    }

    /**
     * Get cities for select options.
     *
     * @return array<int, string>
     */
    public function getSelectOptions(): array
    {
        return (new CityQueryBuilder)->getSelectOptions();
    }
}
