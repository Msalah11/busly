<?php

declare(strict_types=1);

namespace App\Queries\Core;

use App\Contracts\Queries\QueryFilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Abstract base class for building complex database queries with filters.
 * Supports both static and instance method calls.
 *
 * @template TModel of Model
 *
 * @method \Illuminate\Pagination\LengthAwarePaginator<int, TModel> paginate(int $perPage = 15, array<int, string> $columns = ['*'], string $pageName = 'page', ?int $page = null)
 * @method \Illuminate\Pagination\Paginator<int, TModel> simplePaginate(int $perPage = 15, array<int, string> $columns = ['*'], string $pageName = 'page', ?int $page = null)
 * @method \Illuminate\Database\Eloquent\Collection<int, TModel> get(array<int, string> $columns = ['*'])
 * @method TModel|null first(array<int, string> $columns = ['*'])
 * @method static with(array<int, string>|string $relations)
 * @method static where(string|callable $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static orWhere(string $column, mixed $operator = null, mixed $value = null)
 * @method static whereHas(string $relation, callable $callback = null)
 * @method static orWhereHas(string $relation, callable $callback = null)
 * @method static when(mixed $value, callable $callback)
 * @method static orderBy(string $column, string $direction = 'asc')
 * @method static limit(int $value)
 * @method static take(int $value)
 * @method static skip(int $value)
 * @method static count(string $columns = '*')
 * @method TModel findOrFail(int|string $id, array<int, string> $columns = ['*'])
 * @method mixed sum(string $column)
 */
abstract class AbstractQueryBuilder
{
    /**
     * The filters to be applied to the query.
     *
     * @var array<QueryFilterInterface<TModel>>
     */
    protected array $filters = [];

    /**
     * Static instance for method chaining.
     *
     * @var static|null
     */
    protected static ?self $instance = null;

    /**
     * The default columns to select if none are specified.
     */
    protected const DEFAULT_COLUMNS = ['*'];

    /**
     * The default pagination size.
     */
    protected const DEFAULT_PER_PAGE = 15;

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
     * Handle static method calls by creating an instance and calling the method.
     *
     * @param  array<mixed>  $arguments
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        $instance = new static;

        // If the method exists on the instance, call it
        if (method_exists($instance, $method)) {
            return $instance->$method(...$arguments);
        }

        // If it's a Laravel Builder method, get the builder and call it
        $builder = $instance->build();
        if (method_exists($builder, $method)) {
            return $builder->$method(...$arguments);
        }

        throw new \BadMethodCallException(sprintf('Method %s does not exist on ', $method).static::class);
    }

    /**
     * Handle dynamic method calls - proxy to Laravel Builder if method doesn't exist.
     *
     * @param  array<mixed>  $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        // Get the Laravel Builder and call the method on it
        $builder = $this->build();

        if (method_exists($builder, $method)) {
            return $builder->$method(...$arguments);
        }

        throw new \BadMethodCallException(sprintf('Method %s does not exist on ', $method).static::class);
    }

    /**
     * Get the model class for this query builder.
     * Must be implemented by concrete classes.
     *
     * @return class-string<TModel>
     */
    abstract protected static function getModelClass(): string;

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
     */
    final public function addFilter(QueryFilterInterface $filter): self
    {
        $this->filters[] = $filter;

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
     * Apply a callback when the given condition is true.
     * This maintains the query builder chain unlike Laravel's when() method.
     *
     * @param  mixed  $condition
     * @param  callable(static): static  $callback
     */
    final public function when($condition, callable $callback): static
    {
        if ($condition) {
            return $callback($this);
        }

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

        // Apply each filter
        foreach ($this->filters as $filter) {
            $query = $filter->apply($query);
        }

        return $query;
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
