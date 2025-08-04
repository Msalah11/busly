<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\ReservationSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReservationSeat>
 */
class ReservationSeatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ReservationSeat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $seatCounter = 1;
        
        return [
            'reservation_id' => Reservation::factory(),
            'seat_number' => $seatCounter++,
        ];
    }
}