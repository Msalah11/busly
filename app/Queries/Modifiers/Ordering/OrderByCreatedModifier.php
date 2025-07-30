<?php

declare(strict_types=1);

namespace App\Queries\Modifiers\Ordering;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Query modifier for ordering users by creation date.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class OrderByCreatedModifier extends AbstractQueryFilter
{
    /**
     * Valid sort directions.
     */
    private const VALID_DIRECTIONS = ['asc', 'desc'];

    /**
     * @param  string  $direction  The sort direction (asc or desc)
     *
     * @throws InvalidArgumentException If direction is invalid
     */
    public function __construct(
        private readonly string $direction = 'desc'
    ) {
        if (! in_array(strtolower($this->direction), self::VALID_DIRECTIONS, true)) {
            throw new InvalidArgumentException(
                sprintf("Invalid sort direction '%s'. Must be 'asc' or 'desc'.", $this->direction)
            );
        }
    }

    /**
     * Apply the creation date ordering to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->orderBy('created_at', $this->direction);
    }
}
