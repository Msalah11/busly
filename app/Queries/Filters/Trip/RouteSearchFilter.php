<?php

declare(strict_types=1);

namespace App\Queries\Filters\Trip;

use App\Contracts\Queries\QueryFilterInterface;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter trips by searching city names in origin and destination.
 *
 * @implements QueryFilterInterface<Trip>
 */
final class RouteSearchFilter implements QueryFilterInterface
{
    public function __construct(
        private readonly string $searchTerm
    ) {}

    /**
     * Apply the filter to the query.
     *
     * @param  Builder<Trip>  $query
     * @return Builder<Trip>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereHas('originCity', function ($cityQuery) {
                $cityQuery->where('name', 'like', "%{$this->searchTerm}%")
                         ->orWhere('code', 'like', "%{$this->searchTerm}%");
            })->orWhereHas('destinationCity', function ($cityQuery) {
                $cityQuery->where('name', 'like', "%{$this->searchTerm}%")
                         ->orWhere('code', 'like', "%{$this->searchTerm}%");
            });
        });
    }
}
