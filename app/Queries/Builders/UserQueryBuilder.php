<?php

declare(strict_types=1);

namespace App\Queries\Builders;

use App\Models\User;
use App\Queries\Core\AbstractQueryBuilder;
use App\Queries\Filters\Common\DateRangeFilter;
use App\Queries\Filters\Common\SearchFilter;
use App\Queries\Filters\User\ActiveFilter;
use App\Queries\Filters\User\UserRoleFilter;
use App\Queries\Filters\User\VerifiedFilter;
use App\Queries\Modifiers\Ordering\OrderByCreatedModifier;
use App\Queries\Modifiers\Ordering\OrderByNameModifier;
use Carbon\CarbonInterface;

/**
 * Query builder for User model with common filters and methods.
 *
 * @extends AbstractQueryBuilder<User>
 */
final class UserQueryBuilder extends AbstractQueryBuilder
{
    /**
     * Create a new UserQueryBuilder instance.
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
     * @return class-string<User>
     */
    protected static function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Add a search filter for users.
     *
     * @param  array<int, string>  $columns
     * @return $this
     */
    public function search(?string $searchTerm, array $columns = ['name', 'email'], bool $caseSensitive = false): self
    {
        if ($searchTerm !== null) {
            $this->addFilter(new SearchFilter($searchTerm, $columns, $caseSensitive));
        }

        return $this;
    }

    /**
     * Filter users by email verification status.
     *
     * @return $this
     */
    public function verified(bool $verified = true): self
    {
        $this->addFilter(new VerifiedFilter($verified));

        return $this;
    }

    /**
     * Filter users by creation date range.
     *
     * @param  CarbonInterface|string|null  $startDate
     * @param  CarbonInterface|string|null  $endDate
     * @return $this
     */
    public function createdBetween($startDate = null, $endDate = null, bool $inclusive = true): self
    {
        if ($startDate !== null || $endDate !== null) {
            $this->addFilter(new DateRangeFilter('created_at', $startDate, $endDate, $inclusive));
        }

        return $this;
    }

    /**
     * Filter users created today.
     *
     * @return $this
     */
    public function createdToday(): self
    {
        return $this->createdBetween(now()->startOfDay(), now()->endOfDay());
    }

    /**
     * Filter users created this week.
     *
     * @return $this
     */
    public function createdThisWeek(): self
    {
        return $this->createdBetween(now()->startOfWeek(), now()->endOfWeek());
    }

    /**
     * Filter users created this month.
     *
     * @return $this
     */
    public function createdThisMonth(): self
    {
        return $this->createdBetween(now()->startOfMonth(), now()->endOfMonth());
    }

    /**
     * Filter users created in the last N days.
     */
    public function recentDays(int $days = 7): self
    {
        return $this->createdBetween(now()->subDays($days), now());
    }

    /**
     * Filter users by role.
     */
    public function withRole(string $role): self
    {
        $this->addFilter(new UserRoleFilter($role));

        return $this;
    }

    /**
     * Get user statistics.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return [
            'total' => (new self)->get()->count(),
            'admins' => (new self)->withRole('admin')->get()->count(),
            'regular' => (new self)->withRole('user')->get()->count(),
            'recent' => (new self)->recentDays(7)->get()->count(),
        ];
    }

    /**
     * Order users by name.
     *
     * @return $this
     */
    public function orderByName(string $direction = 'asc'): self
    {
        $this->addFilter(new OrderByNameModifier($direction));

        return $this;
    }

    /**
     * Order users by creation date.
     *
     * @return $this
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        $this->addFilter(new OrderByCreatedModifier($direction));

        return $this;
    }

    /**
     * Get active users (those who have verified their email and created recently).
     *
     * @return $this
     */
    public function active(int $daysBack = 30, bool $requireVerified = true): self
    {
        $this->addFilter(new ActiveFilter($daysBack, $requireVerified));

        return $this;
    }
}
