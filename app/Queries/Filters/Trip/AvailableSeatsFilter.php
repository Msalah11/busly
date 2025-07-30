<?php

declare(strict_types=1);

namespace App\Queries\Filters\Trip;

use App\Enums\ReservationStatus;
use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Filter for filtering trips with available seats.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class AvailableSeatsFilter extends AbstractQueryFilter
{
    /**
     * @param  int  $seatsNeeded  The number of seats needed
     *
     * @throws InvalidArgumentException If seats needed is not positive
     */
    public function __construct(
        private readonly int $seatsNeeded = 1
    ) {
        if ($this->seatsNeeded <= 0) {
            throw new InvalidArgumentException('Seats needed must be a positive integer, got '.$this->seatsNeeded);
        }
    }

    /**
     * Apply the available seats filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->whereHas('bus', function ($busQuery): void {
            $busQuery->whereRaw('
                capacity - (
                    SELECT COALESCE(SUM(seats_count), 0)
                    FROM reservations
                    WHERE reservations.trip_id = trips.id
                    AND reservations.status = ?
                ) >= ?
            ', [ReservationStatus::CONFIRMED->value, $this->seatsNeeded]);
        });
    }
}
