<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Trip;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * DTO for trip create/update operations.
 */
final readonly class TripData
{
    public function __construct(
        public int $originCityId,
        public int $destinationCityId,
        public Carbon $departureTime,
        public Carbon $arrivalTime,
        public float $price,
        public int $busId,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            originCityId: $request->integer('origin_city_id'),
            destinationCityId: $request->integer('destination_city_id'),
            departureTime: Carbon::createFromFormat('H:i', (string) $request->string('departure_time')),
            arrivalTime: Carbon::createFromFormat('H:i', (string) $request->string('arrival_time')),
            price: (float) (string) $request->string('price'),
            busId: $request->integer('bus_id'),
            isActive: $request->boolean('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'origin_city_id' => $this->originCityId,
            'destination_city_id' => $this->destinationCityId,
            'departure_time' => $this->departureTime,
            'arrival_time' => $this->arrivalTime,
            'price' => $this->price,
            'bus_id' => $this->busId,
            'is_active' => $this->isActive,
        ];
    }
}
