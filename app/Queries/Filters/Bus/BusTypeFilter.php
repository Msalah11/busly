<?php

declare(strict_types=1);

namespace App\Queries\Filters\Bus;

use App\Enums\BusType;
use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering buses by type.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class BusTypeFilter extends AbstractQueryFilter
{
    /**
     * @param  BusType  $type  The bus type to filter by
     */
    public function __construct(
        private readonly BusType $type
    ) {}

    /**
     * Apply the bus type filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('type', $this->type->value);
    }
}
