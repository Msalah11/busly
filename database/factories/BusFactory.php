<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BusType;
use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bus>
 */
class BusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Bus>
     */
    protected $model = Bus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bus_code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'capacity' => $this->faker->numberBetween(20, 60),
            'type' => $this->faker->randomElement(BusType::cases()),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the bus is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the bus is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a VIP bus.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => BusType::VIP,
            'capacity' => $this->faker->numberBetween(30, 45),
        ]);
    }

    /**
     * Create a standard bus.
     */
    public function standard(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => BusType::STANDARD,
            'capacity' => $this->faker->numberBetween(40, 60),
        ]);
    }
}
