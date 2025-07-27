<?php

declare(strict_types=1);

namespace App\Queries\Core;

use App\Contracts\Queries\QueryFilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract base class for query filters.
 *
 * @template TModel of Model
 *
 * @implements QueryFilterInterface<TModel>
 */
abstract class AbstractQueryFilter implements QueryFilterInterface
{
    /**
     * The default priority for filters.
     */
    protected const DEFAULT_PRIORITY = 100;

    /**
     * Apply the filter to the given query builder.
     *
     * @param  Builder<TModel>  $query  The query builder to modify
     * @return Builder<TModel> The modified query builder
     */
    abstract public function apply(Builder $query): Builder;

    /**
     * Get a unique identifier for this filter.
     *
     * @return string A unique identifier for this filter
     */
    public function getIdentifier(): string
    {
        return static::class;
    }

    /**
     * Determine if this filter should be applied based on its current state.
     *
     * @return bool True if the filter should be applied, false otherwise
     */
    public function shouldApply(): bool
    {
        return true;
    }

    /**
     * Get the priority of this filter.
     *
     * @return int The priority of this filter (higher values = higher priority)
     */
    public function getPriority(): int
    {
        return static::DEFAULT_PRIORITY;
    }

    /**
     * Get metadata about this filter for debugging and logging.
     *
     * @return array<string, mixed> Metadata about this filter
     */
    public function getMetadata(): array
    {
        return [
            'class' => static::class,
            'identifier' => $this->getIdentifier(),
            'priority' => $this->getPriority(),
            'should_apply' => $this->shouldApply(),
        ];
    }
}
