<?php

declare(strict_types=1);

namespace App\Queries\Filters\Reservation;

use App\Enums\ReservationStatus;
use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering reservations by status.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class ReservationStatusFilter extends AbstractQueryFilter
{
    /**
     * @param  ReservationStatus  $status  The reservation status to filter by
     */
    public function __construct(
        private readonly ReservationStatus $status
    ) {}

    /**
     * Apply the reservation status filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('status', $this->status->value);
    }
}
