<?php

declare(strict_types=1);

namespace App\Queries\Filters\Trip;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering trips by active status.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class ActiveTripFilter extends AbstractQueryFilter
{
    /**
     * @param  bool  $active  Whether to filter for active or inactive trips
     */
    public function __construct(
        private readonly bool $active = true
    ) {}

    /**
     * Apply the active trip filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('is_active', $this->active);
    }
}
