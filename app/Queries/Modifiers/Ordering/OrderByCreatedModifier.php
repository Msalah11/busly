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

    /**
     * Get a unique identifier for this modifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'order_by_created_'.strtolower($this->direction);
    }

    /**
     * Get the priority of this modifier.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 50;
    }

    /**
     * Get metadata about this modifier.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return array_merge(parent::getMetadata(), [
            'direction' => $this->direction,
            'column' => 'created_at',
            'type' => 'ordering_modifier',
        ]);
    }
}
