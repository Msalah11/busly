<?php

declare(strict_types=1);

namespace App\Queries\Filters\Bus;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering buses by active status.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class ActiveBusFilter extends AbstractQueryFilter
{
    /**
     * @param  bool  $active  Whether to filter for active or inactive buses
     */
    public function __construct(
        private readonly bool $active = true
    ) {}

    /**
     * Apply the active bus filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('is_active', $this->active);
    }
}
