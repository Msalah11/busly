<?php

declare(strict_types=1);

namespace App\Queries\Builders;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Queries\Core\AbstractQueryBuilder;
use App\Queries\Filters\Common\DateRangeFilter;
use App\Queries\Filters\Common\RecentDaysFilter;
use App\Queries\Filters\Common\SearchFilter;
use App\Queries\Filters\Reservation\ReservationStatusFilter;
use App\Queries\Filters\Reservation\TripReservationFilter;
use App\Queries\Filters\Reservation\UpcomingReservationFilter;
use App\Queries\Filters\Reservation\UserReservationFilter;
use App\Queries\Modifiers\Ordering\OrderByCreatedModifier;
use App\Queries\Modifiers\Relations\RelationModifier;
use Carbon\CarbonInterface;

/**
 * Query builder for Reservation model with common filters and methods.
 *
 * @extends AbstractQueryBuilder<Reservation>
 */
final class ReservationQueryBuilder extends AbstractQueryBuilder
{
    /**
     * Create a new ReservationQueryBuilder instance.
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
     * @return class-string<Reservation>
     */
    protected static function getModelClass(): string
    {
        return Reservation::class;
    }

    /**
     * Add a search filter for reservations.
     *
     * @param  array<int, string>  $columns
     * @return $this
     */
    public function search(?string $searchTerm, array $columns = ['reservation_code'], bool $caseSensitive = false): self
    {
        if ($searchTerm !== null) {
            $this->addFilter(new SearchFilter($searchTerm, $columns, $caseSensitive));
        }

        return $this;
    }

    /**
     * Filter reservations by status.
     *
     * @return $this
     */
    public function withStatus(ReservationStatus $status): self
    {
        $this->addFilter(new ReservationStatusFilter($status));

        return $this;
    }

    /**
     * Filter confirmed reservations.
     *
     * @return $this
     */
    public function confirmed(): self
    {
        return $this->withStatus(ReservationStatus::CONFIRMED);
    }

    /**
     * Filter cancelled reservations.
     *
     * @return $this
     */
    public function cancelled(): self
    {
        return $this->withStatus(ReservationStatus::CANCELLED);
    }

    /**
     * Filter reservations by user.
     *
     * @return $this
     */
    public function forUser(int $userId): self
    {
        $this->addFilter(new UserReservationFilter($userId));

        return $this;
    }

    /**
     * Filter reservations by trip.
     *
     * @return $this
     */
    public function forTrip(int $tripId): self
    {
        $this->addFilter(new TripReservationFilter($tripId));

        return $this;
    }

    /**
     * Filter upcoming reservations (where trip hasn't departed yet).
     *
     * @return $this
     */
    public function upcoming(bool $upcoming = true): self
    {
        $this->addFilter(new UpcomingReservationFilter($upcoming));

        return $this;
    }

    /**
     * Filter reservations by reservation date range.
     *
     * @param  CarbonInterface|string|null  $startDate
     * @param  CarbonInterface|string|null  $endDate
     * @return $this
     */
    public function reservedBetween($startDate = null, $endDate = null, bool $inclusive = true): self
    {
        if ($startDate !== null || $endDate !== null) {
            $this->addFilter(new DateRangeFilter('reserved_at', $startDate, $endDate, $inclusive));
        }

        return $this;
    }

    /**
     * Order reservations by creation date.
     *
     * @return $this
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        $this->addFilter(new OrderByCreatedModifier($direction));

        return $this;
    }

    /**
     * Filter reservations created today.
     *
     * @return $this
     */
    public function createdToday(): self
    {
        return $this->reservedBetween(now()->startOfDay(), now()->endOfDay());
    }

    /**
     * Filter reservations created in the last N days.
     *
     * @return $this
     */
    public function recentDays(int $days = 7): self
    {
        $this->addFilter(new RecentDaysFilter($days));

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
     * Get reservation statistics.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return [
            'total' => (new self)->get()->count(),
            'confirmed' => (new self)->confirmed()->get()->count(),
            'cancelled' => (new self)->cancelled()->get()->count(),
            'today' => (new self)->createdToday()->get()->count(),
            'this_week' => (new self)->recentDays(7)->get()->count(),
        ];
    }
}
