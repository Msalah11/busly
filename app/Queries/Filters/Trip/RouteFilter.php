<?php

declare(strict_types=1);

namespace App\Queries\Filters\Trip;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering trips by route (origin and destination).
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class RouteFilter extends AbstractQueryFilter
{
    /**
     * @param  string|null  $origin  The origin city to filter by
     * @param  string|null  $destination  The destination city to filter by
     */
    public function __construct(
        private readonly ?string $origin,
        private readonly ?string $destination
    ) {}

    /**
     * Apply the route filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        if ($this->origin !== null) {
            $query->where('origin', $this->origin);
        }

        if ($this->destination !== null) {
            $query->where('destination', $this->destination);
        }

        return $query;
    }
}
