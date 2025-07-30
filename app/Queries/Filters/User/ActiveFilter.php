<?php

declare(strict_types=1);

namespace App\Queries\Filters\User;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for getting active users (verified and created within specified timeframe).
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class ActiveFilter extends AbstractQueryFilter
{
    /**
     * @param  int  $daysBack  Number of days back to consider for "recent" creation
     * @param  bool  $requireVerified  Whether to require email verification
     */
    public function __construct(
        private readonly int $daysBack = 30,
        private readonly bool $requireVerified = true
    ) {}

    /**
     * Apply the active user filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        // Filter for recently created users
        $query->where('created_at', '>=', now()->subDays($this->daysBack));

        // Optionally require email verification
        if ($this->requireVerified) {
            $query->whereNotNull('email_verified_at');
        }

        return $query;
    }
}
