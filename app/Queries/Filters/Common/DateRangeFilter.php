<?php

declare(strict_types=1);

namespace App\Queries\Filters\Common;

use App\Queries\Core\AbstractQueryFilter;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for filtering records by a date range.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class DateRangeFilter extends AbstractQueryFilter
{
    /**
     * @param  string  $column  The date column to filter by
     * @param  CarbonInterface|string|null  $startDate  The start date for the range
     * @param  CarbonInterface|string|null  $endDate  The end date for the range
     * @param  bool  $inclusive  Whether the range should be inclusive of the end date
     */
    public function __construct(
        private readonly string $column,
        private readonly CarbonInterface|string|null $startDate = null,
        private readonly CarbonInterface|string|null $endDate = null,
        private readonly bool $inclusive = true
    ) {}

    /**
     * Apply the date range filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        if (! $this->shouldApply()) {
            return $query;
        }

        if ($this->startDate !== null) {
            $startDate = $this->normalizeDate($this->startDate);
            $query->where($this->column, '>=', $startDate->startOfDay());
        }

        if ($this->endDate !== null) {
            $endDate = $this->normalizeDate($this->endDate);
            $operator = $this->inclusive ? '<=' : '<';
            $date = $this->inclusive ? $endDate->endOfDay() : $endDate->startOfDay();
            $query->where($this->column, $operator, $date);
        }

        return $query;
    }

    /**
     * Get a unique identifier for this filter.
     */
    public function getIdentifier(): string
    {
        return 'date_range_'.$this->column;
    }

    /**
     * Determine if this filter should be applied.
     */
    public function shouldApply(): bool
    {
        return $this->startDate !== null || $this->endDate !== null;
    }

    /**
     * Get the priority of this filter.
     *
     * Date filters typically have medium priority.
     */
    public function getPriority(): int
    {
        return 150;
    }

    /**
     * Get metadata about this filter.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return array_merge(parent::getMetadata(), [
            'column' => $this->column,
            'start_date' => $this->startDate?->toDateString(),
            'end_date' => $this->endDate?->toDateString(),
            'inclusive' => $this->inclusive,
            'has_start_date' => $this->startDate !== null,
            'has_end_date' => $this->endDate !== null,
        ]);
    }

    /**
     * Normalize a date input to a Carbon instance.
     */
    private function normalizeDate(CarbonInterface|string $date): Carbon
    {
        return $date instanceof CarbonInterface ? Carbon::instance($date) : Carbon::parse($date);
    }
}
