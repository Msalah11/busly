<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active buses to assign trips to
        $activeBuses = Bus::where('is_active', true)->get();

        if ($activeBuses->isEmpty()) {
            $this->command->warn('No active buses found. Please run BusSeeder first.');

            return;
        }

        // Popular Egyptian routes with realistic trip data
        $routes = [
            // Cairo routes
            ['origin' => 'Cairo', 'destination' => 'Alexandria', 'distance_km' => 220],
            ['origin' => 'Cairo', 'destination' => 'Luxor', 'distance_km' => 670],
            ['origin' => 'Cairo', 'destination' => 'Aswan', 'distance_km' => 880],
            ['origin' => 'Cairo', 'destination' => 'Hurghada', 'distance_km' => 460],
            ['origin' => 'Cairo', 'destination' => 'Sharm El Sheikh', 'distance_km' => 490],
            ['origin' => 'Cairo', 'destination' => 'Marsa Matrouh', 'distance_km' => 290],

            // Alexandria routes
            ['origin' => 'Alexandria', 'destination' => 'Cairo', 'distance_km' => 220],
            ['origin' => 'Alexandria', 'destination' => 'Marsa Matrouh', 'distance_km' => 240],

            // Upper Egypt routes
            ['origin' => 'Luxor', 'destination' => 'Cairo', 'distance_km' => 670],
            ['origin' => 'Luxor', 'destination' => 'Aswan', 'distance_km' => 210],
            ['origin' => 'Aswan', 'destination' => 'Cairo', 'distance_km' => 880],
            ['origin' => 'Aswan', 'destination' => 'Luxor', 'distance_km' => 210],

            // Red Sea routes
            ['origin' => 'Hurghada', 'destination' => 'Cairo', 'distance_km' => 460],
            ['origin' => 'Sharm El Sheikh', 'destination' => 'Cairo', 'distance_km' => 490],
        ];

        // Time slots for different types of trips
        $timeSlots = [
            'early_morning' => ['departure' => '06:00', 'duration_hours' => [3, 4, 5, 6, 8, 10, 12]],
            'morning' => ['departure' => '08:00', 'duration_hours' => [3, 4, 5, 6, 8, 10, 12]],
            'afternoon' => ['departure' => '14:00', 'duration_hours' => [3, 4, 5, 6, 8, 10, 12]],
            'evening' => ['departure' => '18:00', 'duration_hours' => [3, 4, 5, 6, 8, 10, 12]],
            'night' => ['departure' => '22:00', 'duration_hours' => [6, 8, 10, 12]],
        ];

        foreach ($routes as $route) {
            // Create 2-4 trips per route with different times and buses
            $tripsPerRoute = random_int(2, 4);

            for ($i = 0; $i < $tripsPerRoute; ++$i) {
                $bus = $activeBuses->random();
                $timeSlot = collect($timeSlots)->random();
                $durationHours = collect($timeSlot['duration_hours'])->random();

                // Calculate arrival time
                $departureTime = $timeSlot['departure'];
                $arrivalTime = date('H:i', strtotime($departureTime.' + '.$durationHours.' hours'));

                // Calculate price based on distance and bus type
                $basePrice = $route['distance_km'] * 0.8; // 0.8 EGP per km base rate
                $typeMultiplier = $bus->type->value === 'VIP' ? 1.5 : 1.0;
                $price = round($basePrice * $typeMultiplier, 2);

                // Add some price variation
                $priceVariation = random_int(-10, 20) / 100; // -10% to +20%
                $price = round($price * (1 + $priceVariation), 2);

                // Ensure minimum price
                $price = max($price, 50.00);

                Trip::create([
                    'origin' => $route['origin'],
                    'destination' => $route['destination'],
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                    'price' => $price,
                    'bus_id' => $bus->id,
                    'is_active' => random_int(1, 10) > 1, // 90% chance of being active
                ]);
            }
        }

        // Create additional random trips using factory
        $remainingBuses = $activeBuses->shuffle();

        // Morning trips
        Trip::factory()
            ->count(10)
            ->morning()
            ->sequence(fn ($sequence): array => [
                'bus_id' => $remainingBuses->get($sequence->index % $remainingBuses->count())->id,
            ])
            ->create();

        // Evening trips
        Trip::factory()
            ->count(8)
            ->evening()
            ->sequence(fn ($sequence): array => [
                'bus_id' => $remainingBuses->get($sequence->index % $remainingBuses->count())->id,
            ])
            ->create();

        // Some inactive trips (past or cancelled)
        Trip::factory()
            ->count(5)
            ->inactive()
            ->sequence(fn ($sequence): array => [
                'bus_id' => $remainingBuses->get($sequence->index % $remainingBuses->count())->id,
            ])
            ->create();

        $this->command->info('Created trips for '.count($routes).' routes with multiple time slots.');
        $this->command->info('Total trips created: '.Trip::count());
    }
}
