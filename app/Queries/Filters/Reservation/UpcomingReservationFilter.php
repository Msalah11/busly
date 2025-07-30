<?php

declare(strict_types=1);

namespace App\Queries\Filters\Reservation;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering upcoming reservations (where trip hasn't departed yet).
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class UpcomingReservationFilter extends AbstractQueryFilter
{
    /**
     * @param  bool  $upcoming  Whether to filter for upcoming or past reservations
     */
    public function __construct(
        private readonly bool $upcoming = true
    ) {}

    /**
     * Apply the upcoming reservation filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->whereHas('trip', function ($tripQuery): void {
            if ($this->upcoming) {
                $tripQuery->where('departure_time', '>', now());
            } else {
                $tripQuery->where('departure_time', '<=', now());
            }
        });
    }
}
