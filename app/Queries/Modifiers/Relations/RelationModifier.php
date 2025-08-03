<?php

declare(strict_types=1);

namespace App\Queries\Modifiers\Relations;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Query modifier for eager loading relationships.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class RelationModifier extends AbstractQueryFilter
{
    /**
     * @param  array<int, string>|array<string, \Closure>|string  $relations  The relationships to eager load
     */
    public function __construct(
        private readonly array|string $relations
    ) {}

    /**
     * Apply the eager loading to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->with($this->relations);
    }
}
