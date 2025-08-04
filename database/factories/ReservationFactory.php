<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Reservation>
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reservedAt = $this->faker->dateTimeBetween('-30 days', 'now');
        $seatsCount = $this->faker->numberBetween(1, 4);
        $pricePerSeat = $this->faker->randomFloat(2, 50, 500);

        return [
            'reservation_code' => 'RES-' . strtoupper($this->faker->unique()->bothify('########')),
            'user_id' => User::factory(),
            'trip_id' => Trip::factory(),
            'seats_count' => $seatsCount,
            'total_price' => $seatsCount * $pricePerSeat,
            'status' => $this->faker->randomElement(ReservationStatus::cases()),
            'reserved_at' => $reservedAt,
            'cancelled_at' => null,
        ];
    }

    /**
     * Indicate that the reservation is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReservationStatus::CONFIRMED,
            'cancelled_at' => null,
        ]);
    }

    /**
     * Indicate that the reservation is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes): array {
            $cancelledAt = $this->faker->dateTimeBetween($attributes['reserved_at'], 'now');
            
            return [
                'status' => ReservationStatus::CANCELLED,
                'cancelled_at' => $cancelledAt,
            ];
        });
    }

    /**
     * Set the reservation for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set the reservation for a specific trip.
     */
    public function forTrip(Trip $trip): static
    {
        return $this->state(fn (array $attributes): array => [
            'trip_id' => $trip->id,
            'total_price' => $attributes['seats_count'] * $trip->price,
        ]);
    }

    /**
     * Set a specific number of seats.
     */
    public function withSeats(int $seatsCount): static
    {
        return $this->state(fn (array $attributes): array => [
            'seats_count' => $seatsCount,
            'total_price' => $seatsCount * ($attributes['total_price'] / $attributes['seats_count']),
        ]);
    }

    /**
     * Set a recent reservation (within last 7 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'reserved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }
}