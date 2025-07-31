<?php

declare(strict_types=1);

namespace App\Queries\Builders;

use App\Enums\BusType;
use App\Models\Bus;
use App\Queries\Core\AbstractQueryBuilder;
use App\Queries\Filters\Bus\ActiveBusFilter;
use App\Queries\Filters\Bus\BusTypeFilter;
use App\Queries\Filters\Bus\CapacityFilter;
use App\Queries\Filters\Common\SearchFilter;
use App\Queries\Modifiers\Ordering\OrderByCreatedModifier;

/**
 * Query builder for Bus model with common filters and methods.
 *
 * @extends AbstractQueryBuilder<Bus>
 */
final class BusQueryBuilder extends AbstractQueryBuilder
{
    /**
     * Create a new BusQueryBuilder instance.
     *
     * @param  array<int, string>  $columns
     */
    public function __construct(array $columns = ['*'])
    {
        parent::__construct(Bus::class, $columns);
    }

    /**
     * Create a new BusQueryBuilder instance.
     *
     * @param  array<int, string>  $columns
     */
    public static function make(array $columns = ['*']): static
    {
        return new self($columns);
    }

    /**
     * Add a search filter for buses.
     *
     * @param  array<int, string>  $columns
     * @return $this
     */
    public function search(?string $searchTerm, array $columns = ['bus_code'], bool $caseSensitive = false): self
    {
        if ($searchTerm !== null) {
            $this->addFilter(new SearchFilter($searchTerm, $columns, $caseSensitive));
        }

        return $this;
    }

    /**
     * Filter buses by type.
     *
     * @return $this
     */
    public function ofType(BusType $type): self
    {
        $this->addFilter(new BusTypeFilter($type));

        return $this;
    }

    /**
     * Filter only active buses.
     *
     * @return $this
     */
    public function active(bool $active = true): self
    {
        $this->addFilter(new ActiveBusFilter($active));

        return $this;
    }

    /**
     * Order buses by creation date.
     *
     * @return $this
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        $this->addFilter(new OrderByCreatedModifier($direction));

        return $this;
    }

    /**
     * Get buses with available capacity for a given number of seats.
     *
     * @return $this
     */
    public function withCapacityFor(int $seatsNeeded): self
    {
        $this->addFilter(new CapacityFilter($seatsNeeded));

        return $this;
    }

    /**
     * Check if a bus has active trips.
     */
    public static function hasActiveTrips(Bus $bus): bool
    {
        return $bus->trips()->where('is_active', true)->exists();
    }
}
