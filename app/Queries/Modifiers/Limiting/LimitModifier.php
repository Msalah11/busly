<?php

declare(strict_types=1);

namespace App\Queries\Modifiers\Limiting;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Query modifier for limiting the number of results.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class LimitModifier extends AbstractQueryFilter
{
    /**
     * @param  int  $limit  The maximum number of results to return
     *
     * @throws InvalidArgumentException If limit is not positive
     */
    public function __construct(
        private readonly int $limit
    ) {
        if ($this->limit <= 0) {
            throw new InvalidArgumentException('Limit must be a positive integer, got '.$this->limit);
        }
    }

    /**
     * Apply the limit to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->limit($this->limit);
    }
}
