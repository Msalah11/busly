<?php

declare(strict_types=1);

namespace App\Queries\Filters\Reservation;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Filter for filtering reservations by trip.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class TripReservationFilter extends AbstractQueryFilter
{
    /**
     * @param  int  $tripId  The trip ID to filter by
     *
     * @throws InvalidArgumentException If trip ID is not positive
     */
    public function __construct(
        private readonly int $tripId
    ) {
        if ($this->tripId <= 0) {
            throw new InvalidArgumentException('Trip ID must be a positive integer, got '.$this->tripId);
        }
    }

    /**
     * Apply the trip reservation filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('trip_id', $this->tripId);
    }
}
