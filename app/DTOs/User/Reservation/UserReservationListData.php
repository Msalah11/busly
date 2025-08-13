<?php

declare(strict_types=1);

namespace App\DTOs\User\Reservation;

use App\Enums\ReservationStatus;

/**
 * Data Transfer Object for user reservation list operations.
 */
final readonly class UserReservationListData
{
    public function __construct(
        public ?string $search = null,
        public ?ReservationStatus $status = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public bool $upcomingOnly = false,
        public int $perPage = 10,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc',
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: isset($data['status']) ? ReservationStatus::from($data['status']) : null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            upcomingOnly: (bool) ($data['upcoming_only'] ?? false),
            perPage: (int) ($data['per_page'] ?? 10),
            sortBy: $data['sort_by'] ?? 'created_at',
            sortDirection: $data['sort_direction'] ?? 'desc',
        );
    }

    /**
     * Convert to array for query operations.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status?->value,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'upcoming_only' => $this->upcomingOnly,
            'per_page' => $this->perPage,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ];
    }
}
