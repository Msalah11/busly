<?php

declare(strict_types=1);

namespace App\Queries\Filters\Common;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering records created today.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class CreatedTodayFilter extends AbstractQueryFilter
{
    /**
     * Apply the created today filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }
}
