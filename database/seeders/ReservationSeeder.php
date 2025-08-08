<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating realistic reservation data.
 */
class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating reservations...');

        // Get existing users, trips, and buses
        $users = User::where('role', 'user')->get();
        $trips = Trip::with('bus')->where('is_active', true)->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Creating some users first...');
            $users = User::factory()->count(10)->create();
        }

        if ($trips->isEmpty()) {
            $this->command->warn('No trips found. Please run TripSeeder first.');

            return;
        }

        // Create reservations for different scenarios
        $this->createConfirmedReservations($users, $trips);
        $this->createCancelledReservations($users, $trips);
        $this->createRecentReservations($users, $trips);
        $this->createUpcomingReservations($users, $trips);

        $this->command->info('Reservations created successfully!');
    }

    /**
     * Create confirmed reservations (most common scenario).
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $users
     * @param  \Illuminate\Database\Eloquent\Collection<int, Trip>  $trips
     */
    private function createConfirmedReservations(\Illuminate\Database\Eloquent\Collection $users, \Illuminate\Database\Eloquent\Collection $trips): void
    {
        $this->command->info('Creating confirmed reservations...');

        foreach ($trips->take(15) as $trip) {
            // Create 1-3 reservations per trip, but don't exceed capacity
            $reservationCount = min(
                fake()->numberBetween(1, 3),
                (int) floor($trip->bus->capacity / 2) // Don't fill more than half capacity
            );

            for ($i = 0; $i < $reservationCount; ++$i) {
                $user = $users->random();
                $seatsCount = fake()->numberBetween(1, min(4, $trip->bus->capacity - $this->getReservedSeats($trip)));

                if ($seatsCount <= 0) {
                    break; // No more seats available
                }

                Reservation::factory()
                    ->confirmed()
                    ->forUser($user)
                    ->forTrip($trip)
                    ->withSeats($seatsCount)
                    ->create([
                        'total_price' => $seatsCount * $trip->price,
                        'reserved_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
                    ]);
            }
        }
    }

    /**
     * Create cancelled reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $users
     * @param  \Illuminate\Database\Eloquent\Collection<int, Trip>  $trips
     */
    private function createCancelledReservations(\Illuminate\Database\Eloquent\Collection $users, \Illuminate\Database\Eloquent\Collection $trips): void
    {
        $this->command->info('Creating cancelled reservations...');

        foreach ($trips->take(8) as $trip) {
            $user = $users->random();
            $seatsCount = fake()->numberBetween(1, 3);
            $reservedAt = fake()->dateTimeBetween('-20 days', '-5 days');
            $cancelledAt = fake()->dateTimeBetween($reservedAt, 'now');

            Reservation::factory()
                ->cancelled()
                ->forUser($user)
                ->forTrip($trip)
                ->withSeats($seatsCount)
                ->create([
                    'total_price' => $seatsCount * $trip->price,
                    'reserved_at' => $reservedAt,
                    'cancelled_at' => $cancelledAt,
                ]);
        }
    }

    /**
     * Create recent reservations (last 7 days).
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $users
     * @param  \Illuminate\Database\Eloquent\Collection<int, Trip>  $trips
     */
    private function createRecentReservations(\Illuminate\Database\Eloquent\Collection $users, \Illuminate\Database\Eloquent\Collection $trips): void
    {
        $this->command->info('Creating recent reservations...');

        foreach ($trips->take(10) as $trip) {
            $user = $users->random();
            $seatsCount = fake()->numberBetween(1, 2);

            // Check if there are available seats
            if ($seatsCount > ($trip->bus->capacity - $this->getReservedSeats($trip))) {
                continue;
            }

            $status = fake()->randomElement([ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED]);
            $reservedAt = fake()->dateTimeBetween('-7 days', 'now');

            $reservation = Reservation::factory()
                ->forUser($user)
                ->forTrip($trip)
                ->withSeats($seatsCount)
                ->create([
                    'status' => $status,
                    'total_price' => $seatsCount * $trip->price,
                    'reserved_at' => $reservedAt,
                    'cancelled_at' => $status === ReservationStatus::CANCELLED
                        ? fake()->dateTimeBetween($reservedAt, 'now')
                        : null,
                ]);
        }
    }

    /**
     * Create upcoming reservations (for future trips).
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $users
     * @param  \Illuminate\Database\Eloquent\Collection<int, Trip>  $trips
     */
    private function createUpcomingReservations(\Illuminate\Database\Eloquent\Collection $users, \Illuminate\Database\Eloquent\Collection $trips): void
    {
        $this->command->info('Creating upcoming reservations...');

        // Get trips that are in the future
        $upcomingTrips = $trips->filter(fn ($trip): bool => $trip->departure_time > now());

        foreach ($upcomingTrips->take(12) as $trip) {
            $user = $users->random();
            $seatsCount = fake()->numberBetween(1, 3);

            // Check if there are available seats
            if ($seatsCount > ($trip->bus->capacity - $this->getReservedSeats($trip))) {
                continue;
            }

            Reservation::factory()
                ->confirmed()
                ->forUser($user)
                ->forTrip($trip)
                ->withSeats($seatsCount)
                ->create([
                    'total_price' => $seatsCount * $trip->price,
                    'reserved_at' => fake()->dateTimeBetween('-14 days', 'now'),
                ]);
        }
    }

    /**
     * Get the number of confirmed reserved seats for a trip.
     */
    private function getReservedSeats(Trip $trip): int
    {
        return (int) Reservation::where('trip_id', $trip->id)
            ->where('status', ReservationStatus::CONFIRMED)
            ->sum('seats_count');
    }
}
