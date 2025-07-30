<?php

declare(strict_types=1);

namespace App\Queries\Filters\User;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering users by email verification status.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class VerifiedFilter extends AbstractQueryFilter
{
    /**
     * @param  bool  $verified  Whether to filter for verified or unverified users
     */
    public function __construct(
        private readonly bool $verified = true
    ) {}

    /**
     * Apply the verification filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $this->verified
            ? $query->whereNotNull('email_verified_at')
            : $query->whereNull('email_verified_at');
    }
}
