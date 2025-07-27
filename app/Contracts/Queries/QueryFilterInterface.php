<?php

declare(strict_types=1);

namespace App\Contracts\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface for query filters that can be applied to Eloquent query builders.
 *
 * @template TModel of Model
 */
interface QueryFilterInterface
{
    /**
     * Apply the filter to the given query builder.
     *
     * @param  Builder<TModel>  $query  The query builder to modify
     */
    public function apply(Builder $query): Builder;

    /**
     * Get a unique identifier for this filter.
     *
     * @return string A unique identifier for this filter
     */
    public function getIdentifier(): string;

    /**
     * Determine if this filter should be applied based on its current state.
     *
     * @return bool True if the filter should be applied, false otherwise
     */
    public function shouldApply(): bool;

    /**
     * Get the priority of this filter.
     *
     * @return int The priority of this filter (higher values = higher priority)
     */
    public function getPriority(): int;

    /**
     * Get metadata about this filter for debugging and logging.
     *
     * @return array<string, mixed> Metadata about this filter
     */
    public function getMetadata(): array;
}
