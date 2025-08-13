<?php

declare(strict_types=1);

namespace App\DTOs\User\Reservation;

use App\Enums\ReservationStatus;

/**
 * Data Transfer Object for user reservation operations.
 */
final readonly class ReservationData
{
    public function __construct(
        public int $tripId,
        public int $seatsCount,
        public float $totalPrice,
        public ReservationStatus $status = ReservationStatus::CONFIRMED,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            tripId: (int) $data['trip_id'],
            seatsCount: (int) $data['seats_count'],
            totalPrice: (float) $data['total_price'],
            status: ReservationStatus::CONFIRMED, // Users can only create confirmed reservations
        );
    }

    /**
     * Convert to array for model operations, including the authenticated user ID.
     *
     * @return array<string, mixed>
     */
    public function toArray(int $userId): array
    {
        return [
            'user_id' => $userId,
            'trip_id' => $this->tripId,
            'seats_count' => $this->seatsCount,
            'total_price' => $this->totalPrice,
            'status' => $this->status,
        ];
    }
}
