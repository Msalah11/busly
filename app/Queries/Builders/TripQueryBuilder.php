<?php

declare(strict_types=1);

namespace App\Queries\Builders;

use App\Models\Trip;
use App\Queries\Core\AbstractQueryBuilder;
use App\Queries\Filters\Common\CreatedTodayFilter;
use App\Queries\Filters\Common\DateRangeFilter;
use App\Queries\Filters\Common\RecentDaysFilter;
use App\Queries\Filters\Common\SearchFilter;
use App\Queries\Filters\Trip\ActiveTripFilter;
use App\Queries\Filters\Trip\AvailableSeatsFilter;
use App\Queries\Filters\Trip\DepartureDateFilter;
use App\Queries\Filters\Trip\RouteFilter;
use App\Queries\Filters\Trip\RouteSearchFilter;
use App\Queries\Modifiers\Limiting\LimitModifier;
use App\Queries\Modifiers\Ordering\OrderByCreatedModifier;
use App\Queries\Modifiers\Ordering\OrderByDepartureModifier;
use App\Queries\Modifiers\Relations\RelationModifier;
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
        parent::__construct(self::getModelClass(), $columns);
    }

    /**
     * Get the model class for this query builder.
     *
     * @return class-string<Trip>
     */
    protected static function getModelClass(): string
    {
        return Trip::class;
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
     * Search trips by city names (origin and destination).
     *
     * @return $this
     */
    public function searchByRoute(?string $searchTerm): self
    {
        if ($searchTerm !== null) {
            $this->addFilter(new RouteSearchFilter($searchTerm));
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
     * Filter trips by bus ID.
     *
     * @return $this
     */
    public function forBus(?int $busId): self
    {
        if ($busId !== null) {
            $this->where('bus_id', $busId);
        }

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

    /**
     * Eager load relationships while maintaining the query builder chain.
     *
     * @param  array<int, string>|array<string, \Closure>|string  $relations
     * @return $this
     */
    public function with(array|string $relations): self
    {
        $this->addFilter(new RelationModifier($relations));

        return $this;
    }

    /**
     * Filter trips created today.
     */
    public function createdToday(): self
    {
        $this->addFilter(new CreatedTodayFilter);

        return $this;
    }

    /**
     * Filter trips created in the last N days.
     */
    public function recentDays(int $days = 7): self
    {
        $this->addFilter(new RecentDaysFilter($days));

        return $this;
    }

    /**
     * Get recent trips with their associated bus data.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip>
     */
    public function getRecentWithBus(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $this->addFilter(new OrderByCreatedModifier('desc'));
        $this->addFilter(new LimitModifier($limit));

        return $this->with('bus:id,bus_code,type')
            ->get(['id', 'origin', 'destination', 'departure_time', 'price', 'bus_id', 'is_active', 'created_at']);
    }

    /**
     * Get trip statistics.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return [
            'total' => (new self)->get()->count(),
            'active' => (new self)->active()->get()->count(),
            'inactive' => (new self)->active(false)->get()->count(),
            'today' => (new self)->createdToday()->get()->count(),
            'this_week' => (new self)->recentDays(7)->get()->count(),
        ];
    }
}
