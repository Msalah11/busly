<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<City>
     */
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cityName = $this->faker->city();

        return [
            'name' => $cityName,
            'code' => strtoupper(substr($cityName, 0, 3)),
            'latitude' => $this->faker->latitude(22, 32),
            'longitude' => $this->faker->longitude(25, 37),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the city is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific sort order.
     */
    public function withSortOrder(int $sortOrder): static
    {
        return $this->state(fn (array $attributes): array => [
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Create a major Egyptian city.
     */
    public function majorCity(): static
    {
        $cities = [
            ['name' => 'Cairo', 'code' => 'CAI', 'lat' => 30.0444, 'lng' => 31.2357],
            ['name' => 'Alexandria', 'code' => 'ALX', 'lat' => 31.2001, 'lng' => 29.9187],
            ['name' => 'Giza', 'code' => 'GIZ', 'lat' => 30.0131, 'lng' => 31.2089],
            ['name' => 'Luxor', 'code' => 'LXR', 'lat' => 25.6872, 'lng' => 32.6396],
            ['name' => 'Aswan', 'code' => 'ASW', 'lat' => 24.0889, 'lng' => 32.8998],
        ];

        $city = $this->faker->randomElement($cities);

        return $this->state(fn (array $attributes): array => [
            'name' => $city['name'],
            'code' => $city['code'],
            'latitude' => $city['lat'],
            'longitude' => $city['lng'],
            'sort_order' => 0,
        ]);
    }
}
