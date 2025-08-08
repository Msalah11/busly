<?php

declare(strict_types=1);

namespace App\Queries\Builders;

use App\Models\City;
use App\Queries\Core\AbstractQueryBuilder;
use App\Queries\Filters\Common\SearchFilter;
use App\Queries\Modifiers\Ordering\OrderByCreatedModifier;
use App\Queries\Modifiers\Relations\RelationModifier;

/**
 * Query builder for City model with common filters and methods.
 *
 * @extends AbstractQueryBuilder<City>
 */
class CityQueryBuilder extends AbstractQueryBuilder
{
    public function __construct(array $columns = ['*'])
    {
        parent::__construct(City::class, $columns);
    }

    /**
     * Get the model class for this query builder.
     *
     * @return class-string<City>
     */
    protected static function getModelClass(): string
    {
        return City::class;
    }

    /**
     * Filter cities by search term.
     *
     * @param  array<string>  $columns
     * @return $this
     */
    public function search(?string $search, array $columns = ['name', 'code']): self
    {
        if ($search !== null) {
            $this->addFilter(new SearchFilter($search, $columns));
        }

        return $this;
    }

    /**
     * Filter cities by active status.
     *
     * @return $this
     */
    public function active(bool $active = true): self
    {
        $this->addFilter(new class($active) implements \App\Contracts\Queries\QueryFilterInterface
        {
            public function __construct(private readonly bool $active) {}

            public function apply(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
            {
                return $query->where('is_active', $this->active);
            }
        });

        return $this;
    }

    /**
     * Filter inactive cities.
     *
     * @return $this
     */
    public function inactive(): self
    {
        return $this->active(false);
    }

    /**
     * Order cities by sort order and name.
     *
     * @return $this
     */
    public function ordered(): self
    {
        $this->addFilter(new class implements \App\Contracts\Queries\QueryFilterInterface
        {
            public function apply(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
            {
                return $query->orderBy('sort_order')->orderBy('name');
            }
        });

        return $this;
    }

    /**
     * Order cities by creation date.
     *
     * @return $this
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        $this->addFilter(new OrderByCreatedModifier($direction));

        return $this;
    }

    /**
     * Order cities by name.
     *
     * @return $this
     */
    public function orderByName(string $direction = 'asc'): self
    {
        $this->addFilter(new class($direction) implements \App\Contracts\Queries\QueryFilterInterface
        {
            public function __construct(private readonly string $direction) {}

            public function apply(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
            {
                return $query->orderBy('name', $this->direction);
            }
        });

        return $this;
    }

    /**
     * Order cities by a specific column.
     *
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->addFilter(new class($column, $direction) implements \App\Contracts\Queries\QueryFilterInterface
        {
            public function __construct(
                private readonly string $column,
                private readonly string $direction
            ) {}

            public function apply(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
            {
                return $query->orderBy($this->column, $this->direction);
            }
        });

        return $this;
    }

    /**
     * Eager load relationships while maintaining the query builder chain.
     *
     * @param  array<int, string>|array<string, \Closure>|string  $relations
     * @return $this
     */
    public function with(array|string $relations): self
    {
        $this->addFilter(new RelationModifier($relations));

        return $this;
    }

    /**
     * Get city statistics.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return [
            'total' => (new self)->get()->count(),
            'active' => (new self)->active()->get()->count(),
            'inactive' => (new self)->inactive()->get()->count(),
        ];
    }

    /**
     * Get cities for select options.
     *
     * @return array<int, string>
     */
    public function getSelectOptions(): array
    {
        return (new self)
            ->active()
            ->ordered()
            ->get(['id', 'name'])
            ->pluck('name', 'id')
            ->toArray();
    }
}
