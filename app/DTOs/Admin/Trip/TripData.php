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
        public string $origin,
        public string $destination,
        public Carbon $departureTime,
        public Carbon $arrivalTime,
        public float $price,
        public int $busId,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            origin: (string) $request->string('origin'),
            destination: (string) $request->string('destination'),
            departureTime: Carbon::parse((string) $request->string('departure_time')),
            arrivalTime: Carbon::parse((string) $request->string('arrival_time')),
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
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure_time' => $this->departureTime,
            'arrival_time' => $this->arrivalTime,
            'price' => $this->price,
            'bus_id' => $this->busId,
            'is_active' => $this->isActive,
        ];
    }
}
