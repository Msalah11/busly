<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Bus;

use App\Enums\BusType;
use Illuminate\Http\Request;

/**
 * DTO for admin bus list parameters.
 */
final readonly class AdminBusListData
{
    public function __construct(
        public ?string $search = null,
        public ?BusType $type = null,
        public ?bool $active = null,
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string('search')->toString() ?: null,
            type: $request->has('type') ? BusType::from($request->string('type')->toString()) : null,
            active: $request->has('active') ? $request->boolean('active') : null,
            perPage: min(max($request->integer('per_page', 15), 1), 100),
            page: max($request->integer('page', 1), 1),
        );
    }

    public function hasSearch(): bool
    {
        return $this->search !== null && $this->search !== '' && $this->search !== '0';
    }

    public function hasType(): bool
    {
        return $this->type instanceof BusType;
    }

    public function hasActive(): bool
    {
        return $this->active === true;
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

        if ($this->hasType()) {
            $filters['type'] = $this->type;
        }

        if ($this->hasActive()) {
            $filters['active'] = $this->active;
        }

        return $filters;
    }
}
