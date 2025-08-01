<?php

declare(strict_types=1);

namespace App\DTOs\Admin;

use Illuminate\Http\Request;

/**
 * DTO for admin trip list parameters.
 */
final readonly class AdminTripListData
{
    public function __construct(
        public ?string $search = null,
        public ?int $busId = null,
        public ?bool $active = null,
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string('search')->toString() ?: null,
            busId: $request->has('bus_id') ? $request->integer('bus_id') : null,
            active: $request->has('active') ? $request->boolean('active') : null,
            perPage: min(max($request->integer('per_page', 15), 1), 100),
            page: max($request->integer('page', 1), 1),
        );
    }

    public function hasSearch(): bool
    {
        return $this->search !== null && $this->search !== '';
    }

    public function hasBusId(): bool
    {
        return $this->busId !== null;
    }

    public function hasActive(): bool
    {
        return $this->active !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        $filters = [];

        if ($this->hasSearch()) {
            $filters['search'] = $this->search;
        }

        if ($this->hasBusId()) {
            $filters['bus_id'] = $this->busId;
        }

        if ($this->hasActive()) {
            $filters['active'] = $this->active;
        }

        return $filters;
    }
}
