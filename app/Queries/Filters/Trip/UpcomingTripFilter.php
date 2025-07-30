<?php

declare(strict_types=1);

namespace App\Queries\Filters\Trip;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering upcoming trips (not yet departed).
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class UpcomingTripFilter extends AbstractQueryFilter
{
    /**
     * @param  bool  $upcoming  Whether to filter for upcoming or past trips
     */
    public function __construct(
        private readonly bool $upcoming = true
    ) {}

    /**
     * Apply the upcoming trip filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        if ($this->upcoming) {
            return $query->where('departure_time', '>', now());
        }

        return $query->where('departure_time', '<=', now());
    }
} 