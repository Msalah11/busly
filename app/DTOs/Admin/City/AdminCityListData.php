<?php

declare(strict_types=1);

namespace App\DTOs\Admin\City;

/**
 * Data Transfer Object for City list filtering and searching.
 */
final readonly class AdminCityListData
{
    public function __construct(
        public ?string $search = null,
        public ?bool $isActive = null,
        public string $sortBy = 'sort_order',
        public string $sortDirection = 'asc',
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        $isActive = null;
        if (isset($data['is_active']) && $data['is_active'] !== '' && $data['is_active'] !== 'all') {
            $isActive = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        return new self(
            search: !empty($data['search']) ? (string) $data['search'] : null,
            isActive: $isActive,
            sortBy: !empty($data['sort_by']) ? (string) $data['sort_by'] : 'sort_order',
            sortDirection: !empty($data['sort_direction']) && in_array($data['sort_direction'], ['asc', 'desc'])
                ? (string) $data['sort_direction']
                : 'asc',
        );
    }

    /**
     * Check if any search criteria is provided.
     */
    public function hasSearch(): bool
    {
        return $this->search !== null;
    }

    /**
     * Check if active filter is provided.
     */
    public function hasActiveFilter(): bool
    {
        return $this->isActive !== null;
    }

    /**
     * Get valid sortable columns.
     *
     * @return array<string>
     */
    public static function getSortableColumns(): array
    {
        return ['name', 'code', 'sort_order', 'created_at'];
    }

    /**
     * Check if the sort column is valid.
     */
    public function isValidSortColumn(): bool
    {
        return in_array($this->sortBy, self::getSortableColumns());
    }
}
