<?php

declare(strict_types=1);

namespace App\Queries\Core;

use App\Contracts\Queries\QueryFilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Abstract base class for building complex database queries with filters.
 *
 * @template TModel of Model
 */
abstract class AbstractQueryBuilder
{
    /**
     * The filters to be applied to the query, indexed by identifier.
     *
     * @var array<string, QueryFilterInterface<TModel>>
     */
    protected array $filters = [];

    /**
     * The default columns to select if none are specified.
     */
    protected const DEFAULT_COLUMNS = ['*'];

    /**
     * The default pagination size.
     */
    protected const DEFAULT_PER_PAGE = 15;

    /**
     * Whether to enable query logging for debugging.
     */
    protected bool $enableQueryLogging = false;

    /**
     * @param  class-string<TModel>  $modelClass  The model class name
     * @param  array<int, string>  $columns  The columns to be selected
     */
    public function __construct(
        protected readonly string $modelClass,
        protected array $columns = self::DEFAULT_COLUMNS
    ) {
        $this->validateModelClass();
    }

    /**
     * Create a new query builder instance for the specified model.
     *
     * @param  class-string<TModel>  $modelClass
     * @param  array<int, string>  $columns
     */
    public static function for(string $modelClass, array $columns = self::DEFAULT_COLUMNS): static
    {
        return new static($modelClass, $columns);
    }

    /**
     * Add a filter to the query.
     *
     * @param  QueryFilterInterface<TModel>  $filter
     * @return $this
     *
     * @throws InvalidArgumentException If a filter with the same identifier already exists
     */
    final public function addFilter(QueryFilterInterface $filter): self
    {
        $identifier = $filter->getIdentifier();

        if (isset($this->filters[$identifier])) {
            throw new InvalidArgumentException(sprintf("Filter with identifier '%s' already exists", $identifier));
        }

        $this->filters[$identifier] = $filter;

        return $this;
    }

    /**
     * Add multiple filters to the query.
     *
     * @param  array<QueryFilterInterface<TModel>>  $filters
     * @return $this
     */
    final public function addFilters(array $filters): self
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * Clear all filters from the query.
     *
     * @return $this
     */
    final public function clearFilters(): self
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Build the query with all applied filters.
     *
     * @return Builder<TModel>
     */
    final public function build(): Builder
    {
        /** @var Builder<TModel> $query */
        $query = $this->modelClass::query()->select($this->columns);

        // Sort filters by priority (higher priority first)
        $sortedFilters = $this->getSortedFilters();

        // Apply each filter that should be applied
        foreach ($sortedFilters as $filter) {
            if ($filter->shouldApply()) {
                if ($this->enableQueryLogging) {
                    Log::debug('Applying query filter', [
                        'filter' => $filter->getIdentifier(),
                        'metadata' => $filter->getMetadata(),
                    ]);
                }

                $query = $filter->apply($query);
            }
        }

        return $query;
    }

    /**
     * Execute the query and return all results.
     *
     * @return Collection<int, TModel>
     */
    final public function get(): Collection
    {
        return $this->build()->get();
    }

    /**
     * Execute the query and return the first result.
     *
     * @return TModel|null
     */
    final public function first(): ?Model
    {
        return $this->build()->first();
    }

    /**
     * Execute the query and return the first result or fail.
     *
     * @return TModel
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    final public function firstOrFail(): Model
    {
        return $this->build()->firstOrFail();
    }

    /**
     * Execute the query and return paginated results.
     *
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<TModel>
     */
    final public function paginate(
        int $perPage = self::DEFAULT_PER_PAGE,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        return $this->build()->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Execute the query and return simple paginated results.
     *
     * @param  array<int, string>  $columns
     * @return Paginator<TModel>
     */
    final public function simplePaginate(
        int $perPage = self::DEFAULT_PER_PAGE,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): Paginator {
        return $this->build()->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Get debugging information about the query.
     *
     * @return array<string, mixed>
     */
    final public function getDebugInfo(): array
    {
        return [
            'model_class' => $this->modelClass,
            'columns' => $this->columns,
            'filters_count' => count($this->filters),
            'filters' => array_map(
                fn (QueryFilterInterface $filter): array => [
                    'identifier' => $filter->getIdentifier(),
                    'priority' => $filter->getPriority(),
                    'should_apply' => $filter->shouldApply(),
                    'metadata' => $filter->getMetadata(),
                ],
                $this->filters
            ),
            'query_logging_enabled' => $this->enableQueryLogging,
        ];
    }

    /**
     * Sort filters by priority (higher priority first).
     *
     * @return array<QueryFilterInterface<TModel>>
     */
    private function getSortedFilters(): array
    {
        $filters = $this->filters;

        uasort($filters, fn (QueryFilterInterface $a, QueryFilterInterface $b): int => $b->getPriority() <=> $a->getPriority());

        return array_values($filters);
    }

    /**
     * Validate that the model class exists and extends Model.
     *
     * @throws InvalidArgumentException
     */
    private function validateModelClass(): void
    {
        if (! class_exists($this->modelClass)) {
            throw new InvalidArgumentException(sprintf("Model class '%s' does not exist", $this->modelClass));
        }

        if (! is_subclass_of($this->modelClass, Model::class)) {
            throw new InvalidArgumentException(sprintf("Class '%s' must extend ", $this->modelClass).Model::class);
        }
    }
}
