<?php

declare(strict_types=1);

namespace App\Queries\Filters\User;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering users by role.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class UserRoleFilter extends AbstractQueryFilter
{
    /**
     * @param  string  $role  The role to filter by
     */
    public function __construct(
        private readonly string $role
    ) {}

    /**
     * Apply the user role filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('role', $this->role);
    }
}
