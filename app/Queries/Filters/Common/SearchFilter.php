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
        $searchTerm = trim((string) $this->searchTerm);

        // Don't apply if search term is empty
        if (in_array($searchTerm, ['', '0'], true)) {
            return $query;
        }

        $searchValue = sprintf('%%%s%%', $searchTerm);

        return $query->where(function (Builder $subQuery) use ($searchValue): void {
            foreach ($this->searchColumns as $column) {
                if ($this->caseSensitive) {
                    $subQuery->orWhere($column, 'LIKE', $searchValue);
                } else {
                    $subQuery->orWhereRaw('LOWER('.$column.') LIKE LOWER(?)', [$searchValue]);
                }
            }
        });
    }
}
