<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bus;
use App\Models\City;
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

        // Use existing cities if available, otherwise create new ones
        $existingCities = City::pluck('id')->toArray();

        if (count($existingCities) >= 2) {
            // Use existing cities
            $shuffled = collect($existingCities)->shuffle();
            $originCityId = $shuffled->first();
            $destinationCityId = $shuffled->skip(1)->first();
        } else {
            // Create new cities with unique codes by using sequence
            $originCity = City::factory()->sequence(
                ['name' => 'Origin City '.fake()->unique()->randomNumber(5), 'code' => fake()->unique()->lexify('???')]
            )->create();
            $destinationCity = City::factory()->sequence(
                ['name' => 'Destination City '.fake()->unique()->randomNumber(5), 'code' => fake()->unique()->lexify('???')]
            )->create();
            $originCityId = $originCity->id;
            $destinationCityId = $destinationCity->id;
        }

        return [
            'origin_city_id' => $originCityId,
            'destination_city_id' => $destinationCityId,
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
     * Create a trip with specific origin and destination cities.
     */
    public function route(City $originCity, City $destinationCity): static
    {
        return $this->state(fn (array $attributes): array => [
            'origin_city_id' => $originCity->id,
            'destination_city_id' => $destinationCity->id,
        ]);
    }

    /**
     * Create a trip between specific cities by name (for backward compatibility).
     */
    public function routeByName(string $originName, string $destinationName): static
    {
        $originCity = City::where('name', $originName)->first();
        if (! $originCity) {
            $originCity = City::factory()->create([
                'name' => $originName,
                'code' => strtoupper(substr($originName, 0, 3)).fake()->unique()->randomNumber(2),
            ]);
        }

        $destinationCity = City::where('name', $destinationName)->first();
        if (! $destinationCity) {
            $destinationCity = City::factory()->create([
                'name' => $destinationName,
                'code' => strtoupper(substr($destinationName, 0, 3)).fake()->unique()->randomNumber(2),
            ]);
        }

        return $this->state(fn (array $attributes): array => [
            'origin_city_id' => $originCity->id,
            'destination_city_id' => $destinationCity->id,
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
