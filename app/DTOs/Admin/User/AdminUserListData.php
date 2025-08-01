<?php

declare(strict_types=1);

namespace App\DTOs\Admin\User;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for admin user list operations.
 */
final readonly class AdminUserListData
{
    /**
     * Create a new AdminUserListData instance.
     */
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    /**
     * Create AdminUserListData from a request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->filled('search') ? $request->string('search')->toString() : null,
            perPage: $request->integer('per_page', 15),
            page: $request->integer('page', 1),
        );
    }

    /**
     * Check if search is active.
     */
    public function hasSearch(): bool
    {
        return $this->search !== null && $this->search !== '';
    }

    /**
     * Get filters array for query builder.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        $filters = [];

        if ($this->hasSearch()) {
            $filters['search'] = $this->search;
        }

        return $filters;
    }
}
