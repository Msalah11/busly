<?php

declare(strict_types=1);

namespace App\DTOs\Admin\City;

/**
 * Data Transfer Object for City operations.
 */
final readonly class CityData
{
    public function __construct(
        public string $name,
        public string $code,
        public ?float $latitude,
        public ?float $longitude,
        public bool $isActive,
        public int $sortOrder,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            code: strtoupper((string) $data['code']),
            latitude: empty($data['latitude']) ? null : (float) $data['latitude'],
            longitude: empty($data['longitude']) ? null : (float) $data['longitude'],
            isActive: ! empty($data['is_active']) && filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN),
            sortOrder: empty($data['sort_order']) ? 0 : (int) $data['sort_order'],
        );
    }

    /**
     * Convert to array for model operations.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];
    }

    /**
     * Get the display name.
     */
    public function getDisplayName(): string
    {
        return $this->name;
    }
}
