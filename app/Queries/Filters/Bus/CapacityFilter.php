<?php

declare(strict_types=1);

namespace App\Queries\Filters\Bus;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Filter for filtering buses by minimum capacity.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class CapacityFilter extends AbstractQueryFilter
{
    /**
     * @param  int  $minCapacity  The minimum capacity required
     *
     * @throws InvalidArgumentException If capacity is not positive
     */
    public function __construct(
        private readonly int $minCapacity
    ) {
        if ($this->minCapacity <= 0) {
            throw new InvalidArgumentException('Minimum capacity must be a positive integer, got '.$this->minCapacity);
        }
    }

    /**
     * Apply the capacity filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('capacity', '>=', $this->minCapacity);
    }
}
