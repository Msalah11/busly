<?php

declare(strict_types=1);

namespace App\Queries\Filters\Common;

use App\Queries\Core\AbstractQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filter for searching records by a search term across multiple columns.
 *
 * @template TModel of Model
 *
 * @extends AbstractQueryFilter<TModel>
 */
final class SearchFilter extends AbstractQueryFilter
{
    /**
     * @param  string|null  $searchTerm  The search term to filter by
     * @param  array<int, string>  $searchColumns  The columns to search in
     * @param  bool  $caseSensitive  Whether the search should be case sensitive
     */
    public function __construct(
        private readonly ?string $searchTerm,
        private readonly array $searchColumns = ['name'],
        private readonly bool $caseSensitive = false
    ) {}

    /**
     * Apply the search filter to the query.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        if (! $this->shouldApply()) {
            return $query;
        }

        $searchTerm = trim((string) $this->searchTerm);
        $operator = $this->caseSensitive ? 'LIKE' : 'ILIKE';
        $searchValue = sprintf('%%%s%%', $searchTerm);

        return $query->where(function (Builder $subQuery) use ($operator, $searchValue): void {
            foreach ($this->searchColumns as $column) {
                $subQuery->orWhere($column, $operator, $searchValue);
            }
        });
    }

    /**
     * Get a unique identifier for this filter.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'search_'.md5(implode(',', $this->searchColumns));
    }

    /**
     * Determine if this filter should be applied.
     *
     * @return bool
     */
    public function shouldApply(): bool
    {
        return ! in_array(trim($this->searchTerm ?? ''), ['', '0'], true);
    }

    /**
     * Get the priority of this filter.
     *
     * Search filters typically have higher priority to be applied early.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 200;
    }

    /**
     * Get metadata about this filter.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return array_merge(parent::getMetadata(), [
            'search_term' => $this->searchTerm,
            'search_columns' => $this->searchColumns,
            'case_sensitive' => $this->caseSensitive,
            'has_search_term' => ! in_array(trim($this->searchTerm ?? ''), ['', '0'], true),
        ]);
    }
}
