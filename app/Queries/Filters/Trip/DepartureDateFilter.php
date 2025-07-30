<?php

declare(strict_types=1);

namespace App\Queries\Filters\Trip;

use App\Queries\Core\AbstractQueryFilter;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering trips by departure date.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class DepartureDateFilter extends AbstractQueryFilter
{
    private readonly Carbon $date;

    /**
     * @param  CarbonInterface|string  $date  The departure date to filter by
     */
    public function __construct(CarbonInterface|string $date)
    {
        $this->date = Carbon::parse($date);
    }

    /**
     * Apply the departure date filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        return $query->whereDate('departure_time', $this->date->toDateString());
    }
}
