<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Bus;

use App\Enums\BusType;
use Illuminate\Http\Request;

/**
 * DTO for bus create/update operations.
 */
final readonly class BusData
{
    public function __construct(
        public string $busCode,
        public int $capacity,
        public BusType $type,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            busCode: (string) $request->string('bus_code'),
            capacity: $request->integer('capacity'),
            type: BusType::from((string) $request->string('type')),
            isActive: $request->boolean('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'bus_code' => $this->busCode,
            'capacity' => $this->capacity,
            'type' => $this->type,
            'is_active' => $this->isActive,
        ];
    }
}
