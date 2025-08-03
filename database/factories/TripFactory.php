<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Trip>
     */
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureTime = Carbon::createFromFormat('H:i', $this->faker->time('H:i'));
        $arrivalTime = $departureTime->copy()->addHours($this->faker->numberBetween(2, 8));

        return [
            'origin' => $this->faker->city(),
            'destination' => $this->faker->city(),
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'price' => $this->faker->randomFloat(2, 50, 500),
            'bus_id' => Bus::factory(),
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
        ];
    }

    /**
     * Indicate that the trip is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the trip is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a trip with specific origin and destination.
     */
    public function route(string $origin, string $destination): static
    {
        return $this->state(fn (array $attributes): array => [
            'origin' => $origin,
            'destination' => $destination,
        ]);
    }

    /**
     * Create a trip with specific bus.
     */
    public function forBus(Bus $bus): static
    {
        return $this->state(fn (array $attributes): array => [
            'bus_id' => $bus->id,
        ]);
    }

    /**
     * Create a trip with specific times.
     */
    public function withTimes(string $departureTime, string $arrivalTime): static
    {
        return $this->state(fn (array $attributes): array => [
            'departure_time' => Carbon::createFromFormat('H:i', $departureTime),
            'arrival_time' => Carbon::createFromFormat('H:i', $arrivalTime),
        ]);
    }

    /**
     * Create a trip with specific price.
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes): array => [
            'price' => $price,
        ]);
    }

    /**
     * Create a morning trip (departure between 6:00-12:00).
     */
    public function morning(): static
    {
        $departureTime = Carbon::createFromFormat('H:i', $this->faker->time('H:i', '12:00'));
        if ($departureTime->hour < 6) {
            $departureTime->addHours(6);
        }

        $arrivalTime = $departureTime->copy()->addHours($this->faker->numberBetween(2, 6));

        return $this->state(fn (array $attributes): array => [
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
        ]);
    }

    /**
     * Create an evening trip (departure between 18:00-23:00).
     */
    public function evening(): static
    {
        $hour = $this->faker->numberBetween(18, 23);
        $minute = $this->faker->randomElement([0, 15, 30, 45]);
        $departureTime = Carbon::createFromFormat('H:i', sprintf('%02d:%02d', $hour, $minute));
        $arrivalTime = $departureTime->copy()->addHours($this->faker->numberBetween(2, 6));

        return $this->state(fn (array $attributes): array => [
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
        ]);
    }
}
