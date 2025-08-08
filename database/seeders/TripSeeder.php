<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\City;
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

        // Get active cities for creating routes
        $cities = City::where('is_active', true)->get();

        if ($cities->isEmpty()) {
            $this->command->warn('No active cities found. Please run CitySeeder first.');
            return;
        }

        // Create a mapping of city names to IDs for easier route definition
        $cityMap = $cities->pluck('id', 'name')->toArray();

        // Popular Egyptian routes with realistic trip data using city IDs
        $routes = [
            // Cairo routes
            ['origin_city_id' => $cityMap['Cairo'] ?? null, 'destination_city_id' => $cityMap['Alexandria'] ?? null, 'distance_km' => 220],
            ['origin_city_id' => $cityMap['Cairo'] ?? null, 'destination_city_id' => $cityMap['Luxor'] ?? null, 'distance_km' => 670],
            ['origin_city_id' => $cityMap['Cairo'] ?? null, 'destination_city_id' => $cityMap['Aswan'] ?? null, 'distance_km' => 880],
            ['origin_city_id' => $cityMap['Cairo'] ?? null, 'destination_city_id' => $cityMap['Hurghada'] ?? null, 'distance_km' => 460],
            ['origin_city_id' => $cityMap['Cairo'] ?? null, 'destination_city_id' => $cityMap['Sharm El Sheikh'] ?? null, 'distance_km' => 490],
            ['origin_city_id' => $cityMap['Cairo'] ?? null, 'destination_city_id' => $cityMap['Marsa Matruh'] ?? null, 'distance_km' => 290],

            // Alexandria routes
            ['origin_city_id' => $cityMap['Alexandria'] ?? null, 'destination_city_id' => $cityMap['Cairo'] ?? null, 'distance_km' => 220],
            ['origin_city_id' => $cityMap['Alexandria'] ?? null, 'destination_city_id' => $cityMap['Marsa Matruh'] ?? null, 'distance_km' => 240],

            // Upper Egypt routes
            ['origin_city_id' => $cityMap['Luxor'] ?? null, 'destination_city_id' => $cityMap['Cairo'] ?? null, 'distance_km' => 670],
            ['origin_city_id' => $cityMap['Luxor'] ?? null, 'destination_city_id' => $cityMap['Aswan'] ?? null, 'distance_km' => 210],
            ['origin_city_id' => $cityMap['Aswan'] ?? null, 'destination_city_id' => $cityMap['Cairo'] ?? null, 'distance_km' => 880],
            ['origin_city_id' => $cityMap['Aswan'] ?? null, 'destination_city_id' => $cityMap['Luxor'] ?? null, 'distance_km' => 210],

            // Red Sea routes
            ['origin_city_id' => $cityMap['Hurghada'] ?? null, 'destination_city_id' => $cityMap['Cairo'] ?? null, 'distance_km' => 460],
            ['origin_city_id' => $cityMap['Sharm El Sheikh'] ?? null, 'destination_city_id' => $cityMap['Cairo'] ?? null, 'distance_km' => 490],
        ];

        // Filter out routes where cities don't exist
        $routes = array_filter($routes, function ($route) {
            return $route['origin_city_id'] !== null && $route['destination_city_id'] !== null;
        });

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
                    'origin_city_id' => $route['origin_city_id'],
                    'destination_city_id' => $route['destination_city_id'],
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                    'price' => $price,
                    'bus_id' => $bus->id,
                    'is_active' => random_int(1, 10) > 1, // 90% chance of being active
                ]);
            }
        }

        // Log the results
        $totalTrips = Trip::count();
        $routeCount = count($routes);
        $this->command->info("Created trips for {$routeCount} routes with multiple time slots.");
        $this->command->info("Total trips created: {$totalTrips}");
    }
}
