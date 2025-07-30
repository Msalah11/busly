<?php

declare(strict_types=1);

namespace App\Queries\Core;

use App\Contracts\Queries\QueryFilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract base class for query filters.
 *
 * @template TModel of Model
 *
 * @implements QueryFilterInterface<TModel>
 */
abstract class AbstractQueryFilter implements QueryFilterInterface
{
    /**
     * Apply the filter to the given query builder.
     *
     * @param  Builder<TModel>  $query  The query builder to modify
     * @return Builder<TModel> The modified query builder
     */
    abstract public function apply(Builder $query): Builder;
}
