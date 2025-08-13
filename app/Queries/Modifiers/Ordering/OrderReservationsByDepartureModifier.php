<?php

declare(strict_types=1);

namespace App\Queries\Modifiers\Ordering;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Modifier for ordering reservations by their trip's departure time.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class OrderReservationsByDepartureModifier extends AbstractQueryFilter
{
    /**
     * @param  string  $direction  The sort direction ('asc' or 'desc')
     *
     * @throws InvalidArgumentException If direction is not 'asc' or 'desc'
     */
    public function __construct(
        private readonly string $direction = 'asc'
    ) {
        if (! in_array(strtolower($this->direction), ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Direction must be "asc" or "desc", got "'.$this->direction.'"');
        }
    }

    /**
     * Apply the order by trip departure modifier to the reservation query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        // Join with trips table if not already joined
        if (!$this->hasJoin($query, 'trips')) {
            $query->join('trips', 'reservations.trip_id', '=', 'trips.id');
        }
        
        return $query->orderBy('trips.departure_time', $this->direction);
    }

    /**
     * Check if the query already has a join with the specified table.
     *
     * @param  Builder<TModel>  $query
     * @param  string  $table
     * @return bool
     */
    private function hasJoin(Builder $query, string $table): bool
    {
        $joins = $query->getQuery()->joins ?? [];
        
        foreach ($joins as $join) {
            if ($join->table === $table) {
                return true;
            }
        }
        
        return false;
    }
}
