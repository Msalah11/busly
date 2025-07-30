<?php

declare(strict_types=1);

namespace App\Contracts\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface for query filters that can be applied to Eloquent query builders.
 *
 * @template TModel of Model
 */
interface QueryFilterInterface
{
    /**
     * Apply the filter to the given query builder.
     *
     * @param  Builder<TModel>  $query  The query builder to modify
     * @return Builder<TModel> The modified query builder
     */
    public function apply(Builder $query): Builder;
}
