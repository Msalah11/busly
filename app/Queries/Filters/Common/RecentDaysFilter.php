<?php

declare(strict_types=1);

namespace App\Queries\Filters\Common;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Filter for filtering records created in the last N days.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class RecentDaysFilter extends AbstractQueryFilter
{
    /**
     * @param  int  $days  The number of days to look back
     *
     * @throws InvalidArgumentException If days is not positive
     */
    public function __construct(
        private readonly int $days = 7
    ) {
        if ($this->days <= 0) {
            throw new InvalidArgumentException('Days must be a positive integer, got '.$this->days);
        }
    }

    /**
     * Apply the recent days filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($this->days));
    }
}
