<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Reservation;

use App\Enums\ReservationStatus;
use Carbon\Carbon;

/**
 * Data Transfer Object for Reservation operations.
 */
final readonly class ReservationData
{
    public function __construct(
        public int $userId,
        public int $tripId,
        public int $seatsCount,
        public float $totalPrice,
        public ReservationStatus $status,
        public ?Carbon $reservedAt = null,
        public ?Carbon $cancelledAt = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            tripId: (int) $data['trip_id'],
            seatsCount: (int) $data['seats_count'],
            totalPrice: (float) $data['total_price'],
            status: ReservationStatus::from($data['status']),
            reservedAt: isset($data['reserved_at']) ? Carbon::parse($data['reserved_at']) : null,
            cancelledAt: isset($data['cancelled_at']) ? Carbon::parse($data['cancelled_at']) : null,
        );
    }

    /**
     * Convert to array for model operations.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'user_id' => $this->userId,
            'trip_id' => $this->tripId,
            'seats_count' => $this->seatsCount,
            'total_price' => $this->totalPrice,
            'status' => $this->status,
        ];

        if ($this->reservedAt instanceof \Carbon\Carbon) {
            $data['reserved_at'] = $this->reservedAt;
        }

        if ($this->cancelledAt instanceof \Carbon\Carbon) {
            $data['cancelled_at'] = $this->cancelledAt;
        }

        return $data;
    }
}
