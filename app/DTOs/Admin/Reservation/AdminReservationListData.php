<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Reservation;

use App\Enums\ReservationStatus;
use Carbon\Carbon;

/**
 * Data Transfer Object for Reservation list filtering and searching.
 */
final readonly class AdminReservationListData
{
    public function __construct(
        public ?string $search = null,
        public ?ReservationStatus $status = null,
        public ?int $userId = null,
        public ?int $tripId = null,
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            search: empty($data['search']) ? null : (string) $data['search'],
            status: ! empty($data['status']) && $data['status'] !== 'all'
                ? ReservationStatus::from($data['status'])
                : null,
            userId: empty($data['user_id']) ? null : (int) $data['user_id'],
            tripId: empty($data['trip_id']) ? null : (int) $data['trip_id'],
            startDate: empty($data['start_date']) ? null : Carbon::parse($data['start_date']),
            endDate: empty($data['end_date']) ? null : Carbon::parse($data['end_date']),
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
     * Check if status filter is provided.
     */
    public function hasStatus(): bool
    {
        return $this->status instanceof \App\Enums\ReservationStatus;
    }

    /**
     * Check if user filter is provided.
     */
    public function hasUser(): bool
    {
        return $this->userId !== null;
    }

    /**
     * Check if trip filter is provided.
     */
    public function hasTrip(): bool
    {
        return $this->tripId !== null;
    }

    /**
     * Check if date range filter is provided.
     */
    public function hasDateRange(): bool
    {
        return $this->startDate instanceof \Carbon\Carbon || $this->endDate instanceof \Carbon\Carbon;
    }
}
