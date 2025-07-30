<?php

declare(strict_types=1);

namespace App\Queries\Filters\Reservation;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Filter for filtering reservations by user.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class UserReservationFilter extends AbstractQueryFilter
{
    /**
     * @param  int  $userId  The user ID to filter by
     *
     * @throws InvalidArgumentException If user ID is not positive
     */
    public function __construct(
        private readonly int $userId
    ) {
        if ($this->userId <= 0) {
            throw new InvalidArgumentException('User ID must be a positive integer, got '.$this->userId);
        }
    }

    /**
     * Apply the user reservation filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('user_id', $this->userId);
    }
}
