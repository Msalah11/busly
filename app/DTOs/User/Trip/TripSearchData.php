<?php

declare(strict_types=1);

namespace App\DTOs\User\Trip;

/**
 * Data Transfer Object for user trip search operations.
 */
final readonly class TripSearchData
{
    public function __construct(
        public ?int $originCityId = null,
        public ?int $destinationCityId = null,
        public ?string $departureDate = null,
        public ?int $minSeats = null,
        public ?float $maxPrice = null,
        public int $perPage = 12,
        public string $sortBy = 'departure_time',
        public string $sortDirection = 'asc',
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            originCityId: isset($data['origin_city_id']) ? (int) $data['origin_city_id'] : null,
            destinationCityId: isset($data['destination_city_id']) ? (int) $data['destination_city_id'] : null,
            departureDate: $data['departure_date'] ?? null,
            minSeats: isset($data['min_seats']) ? (int) $data['min_seats'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            perPage: (int) ($data['per_page'] ?? 12),
            sortBy: $data['sort_by'] ?? 'departure_time',
            sortDirection: $data['sort_direction'] ?? 'asc',
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
            'origin_city_id' => $this->originCityId,
            'destination_city_id' => $this->destinationCityId,
            'departure_date' => $this->departureDate,
            'min_seats' => $this->minSeats,
            'max_price' => $this->maxPrice,
            'per_page' => $this->perPage,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ];
    }
}
