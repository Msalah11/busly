<?php

declare(strict_types=1);

namespace App\Queries\Builders;

use App\Models\Trip;
use App\Queries\Core\AbstractQueryBuilder;
use App\Queries\Filters\Common\DateRangeFilter;
use App\Queries\Filters\Common\SearchFilter;
use App\Queries\Filters\Trip\ActiveTripFilter;
use App\Queries\Filters\Trip\AvailableSeatsFilter;
use App\Queries\Filters\Trip\DepartureDateFilter;
use App\Queries\Filters\Trip\RouteFilter;
use App\Queries\Filters\Trip\UpcomingTripFilter;
use App\Queries\Modifiers\Ordering\OrderByDepartureModifier;
use Carbon\CarbonInterface;

/**
 * Query builder for Trip model with common filters and methods.
 *
 * @extends AbstractQueryBuilder<Trip>
 */
final class TripQueryBuilder extends AbstractQueryBuilder
{
    /**
     * Create a new TripQueryBuilder instance.
     *
     * @param  array<int, string>  $columns
     */
    public function __construct(array $columns = ['*'])
    {
        parent::__construct(Trip::class, $columns);
    }

    /**
     * Create a new TripQueryBuilder instance.
     *
     * @param  array<int, string>  $columns
     */
    public static function make(array $columns = ['*']): static
    {
        return new self($columns);
    }

    /**
     * Add a search filter for trips.
     *
     * @param  array<int, string>  $columns
     * @return $this
     */
    public function search(?string $searchTerm, array $columns = ['origin', 'destination'], bool $caseSensitive = false): self
    {
        if ($searchTerm !== null) {
            $this->addFilter(new SearchFilter($searchTerm, $columns, $caseSensitive));
        }

        return $this;
    }

    /**
     * Filter trips by route (origin and destination).
     *
     * @return $this
     */
    public function byRoute(?string $origin, ?string $destination): self
    {
        if ($origin !== null || $destination !== null) {
            $this->addFilter(new RouteFilter($origin, $destination));
        }

        return $this;
    }

    /**
     * Filter only active trips.
     *
     * @return $this
     */
    public function active(bool $active = true): self
    {
        $this->addFilter(new ActiveTripFilter($active));

        return $this;
    }

    /**
     * Filter only upcoming trips.
     *
     * @return $this
     */
    public function upcoming(bool $upcoming = true): self
    {
        $this->addFilter(new UpcomingTripFilter($upcoming));

        return $this;
    }

    /**
     * Filter trips by departure date.
     *
     * @param  CarbonInterface|string|null  $date
     * @return $this
     */
    public function byDepartureDate($date): self
    {
        if ($date !== null) {
            $this->addFilter(new DepartureDateFilter($date));
        }

        return $this;
    }

    /**
     * Filter trips by departure date range.
     *
     * @param  CarbonInterface|string|null  $startDate
     * @param  CarbonInterface|string|null  $endDate
     * @return $this
     */
    public function departureBetween($startDate = null, $endDate = null, bool $inclusive = true): self
    {
        if ($startDate !== null || $endDate !== null) {
            $this->addFilter(new DateRangeFilter('departure_time', $startDate, $endDate, $inclusive));
        }

        return $this;
    }

    /**
     * Filter trips with available seats.
     *
     * @return $this
     */
    public function withAvailableSeats(int $seatsNeeded = 1): self
    {
        $this->addFilter(new AvailableSeatsFilter($seatsNeeded));

        return $this;
    }

    /**
     * Order trips by departure time.
     *
     * @return $this
     */
    public function orderByDeparture(string $direction = 'asc'): self
    {
        $this->addFilter(new OrderByDepartureModifier($direction));

        return $this;
    }
}
