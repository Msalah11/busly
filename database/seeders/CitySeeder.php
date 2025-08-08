<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating Egyptian cities data.
 */
class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating Egyptian cities...');

        // Major cities with high priority (sort_order 0-10)
        $majorCities = [
            ['name' => 'Cairo', 'code' => 'CAI', 'lat' => 30.0444, 'lng' => 31.2357, 'sort' => 1],
            ['name' => 'Alexandria', 'code' => 'ALX', 'lat' => 31.2001, 'lng' => 29.9187, 'sort' => 2],
            ['name' => 'Giza', 'code' => 'GIZ', 'lat' => 30.0131, 'lng' => 31.2089, 'sort' => 3],
            ['name' => 'Sharm El Sheikh', 'code' => 'SSH', 'lat' => 27.9158, 'lng' => 34.3300, 'sort' => 4],
            ['name' => 'Hurghada', 'code' => 'HRG', 'lat' => 27.2574, 'lng' => 33.8129, 'sort' => 5],
            ['name' => 'Luxor', 'code' => 'LXR', 'lat' => 25.6872, 'lng' => 32.6396, 'sort' => 6],
            ['name' => 'Aswan', 'code' => 'ASW', 'lat' => 24.0889, 'lng' => 32.8998, 'sort' => 7],
        ];

        // Regional capitals and important cities (sort_order 11-30)
        $regionalCities = [
            ['name' => 'Port Said', 'code' => 'PSD', 'lat' => 31.2653, 'lng' => 32.3019, 'sort' => 11],
            ['name' => 'Suez', 'code' => 'SUZ', 'lat' => 29.9668, 'lng' => 32.5498, 'sort' => 12],
            ['name' => 'Ismailia', 'code' => 'ISM', 'lat' => 30.5965, 'lng' => 32.2715, 'sort' => 13],
            ['name' => 'Mansoura', 'code' => 'MNS', 'lat' => 31.0409, 'lng' => 31.3785, 'sort' => 14],
            ['name' => 'Tanta', 'code' => 'TNT', 'lat' => 30.7865, 'lng' => 31.0004, 'sort' => 15],
            ['name' => 'Zagazig', 'code' => 'ZGZ', 'lat' => 30.5877, 'lng' => 31.5021, 'sort' => 16],
            ['name' => 'Mahalla', 'code' => 'MHL', 'lat' => 30.9734, 'lng' => 31.1668, 'sort' => 17],
            ['name' => 'Damanhur', 'code' => 'DMH', 'lat' => 31.0341, 'lng' => 30.4682, 'sort' => 18],
            ['name' => 'Minya', 'code' => 'MNY', 'lat' => 28.0871, 'lng' => 30.7618, 'sort' => 19],
            ['name' => 'Asyut', 'code' => 'ASY', 'lat' => 27.1809, 'lng' => 31.1837, 'sort' => 20],
            ['name' => 'Sohag', 'code' => 'SOH', 'lat' => 26.5569, 'lng' => 31.6948, 'sort' => 21],
            ['name' => 'Qena', 'code' => 'QEN', 'lat' => 26.1551, 'lng' => 32.7160, 'sort' => 22],
            ['name' => 'Beni Suef', 'code' => 'BNS', 'lat' => 29.0661, 'lng' => 31.0994, 'sort' => 23],
            ['name' => 'Fayyum', 'code' => 'FYM', 'lat' => 29.3084, 'lng' => 30.8428, 'sort' => 24],
        ];

        // Coastal and tourist cities (sort_order 31-50)
        $coastalCities = [
            ['name' => 'Marsa Alam', 'code' => 'RSA', 'lat' => 25.0673, 'lng' => 34.8914, 'sort' => 31],
            ['name' => 'Dahab', 'code' => 'DHB', 'lat' => 28.4957, 'lng' => 34.5136, 'sort' => 32],
            ['name' => 'Nuweiba', 'code' => 'NWB', 'lat' => 29.0342, 'lng' => 34.6681, 'sort' => 33],
            ['name' => 'Taba', 'code' => 'TAB', 'lat' => 29.4868, 'lng' => 34.8821, 'sort' => 34],
            ['name' => 'El Gouna', 'code' => 'GON', 'lat' => 27.3963, 'lng' => 33.6805, 'sort' => 35],
            ['name' => 'Safaga', 'code' => 'SFG', 'lat' => 26.7373, 'lng' => 33.9395, 'sort' => 36],
            ['name' => 'Marsa Matruh', 'code' => 'MUH', 'lat' => 31.3543, 'lng' => 27.2373, 'sort' => 37],
            ['name' => 'El Alamein', 'code' => 'ALM', 'lat' => 30.8333, 'lng' => 28.9500, 'sort' => 38],
            ['name' => 'Rosetta', 'code' => 'RST', 'lat' => 31.3991, 'lng' => 30.4166, 'sort' => 39],
        ];

        // Desert and oasis cities (sort_order 51-70)
        $desertCities = [
            ['name' => 'Siwa Oasis', 'code' => 'SIW', 'lat' => 29.2030, 'lng' => 25.5197, 'sort' => 51],
            ['name' => 'Bahariya Oasis', 'code' => 'BHR', 'lat' => 28.3488, 'lng' => 28.8647, 'sort' => 52],
            ['name' => 'Farafra Oasis', 'code' => 'FRF', 'lat' => 27.0581, 'lng' => 27.9707, 'sort' => 53],
            ['name' => 'Dakhla Oasis', 'code' => 'DKH', 'lat' => 25.5000, 'lng' => 29.0000, 'sort' => 54],
            ['name' => 'Kharga Oasis', 'code' => 'KHG', 'lat' => 25.4419, 'lng' => 30.5341, 'sort' => 55],
            ['name' => 'Abu Simbel', 'code' => 'ABS', 'lat' => 22.3372, 'lng' => 31.6258, 'sort' => 56],
        ];

        // Combine all cities
        $allCities = array_merge($majorCities, $regionalCities, $coastalCities, $desertCities);

        // Create cities
        foreach ($allCities as $cityData) {
            City::create([
                'name' => $cityData['name'],
                'code' => $cityData['code'],
                'latitude' => $cityData['lat'],
                'longitude' => $cityData['lng'],
                'is_active' => true,
                'sort_order' => $cityData['sort'],
            ]);
        }

        // Create a few inactive cities for testing
        $inactiveCities = [
            ['name' => 'Test City 1', 'code' => 'TST1', 'lat' => 30.0, 'lng' => 31.0, 'sort' => 100],
            ['name' => 'Test City 2', 'code' => 'TST2', 'lat' => 30.1, 'lng' => 31.1, 'sort' => 101],
        ];

        foreach ($inactiveCities as $cityData) {
            City::create([
                'name' => $cityData['name'],
                'code' => $cityData['code'],
                'latitude' => $cityData['lat'],
                'longitude' => $cityData['lng'],
                'is_active' => false,
                'sort_order' => $cityData['sort'],
            ]);
        }

        $totalCities = count($allCities) + count($inactiveCities);
        $activeCities = count($allCities);
        $inactiveCities = count($inactiveCities);

        $this->command->info("Created {$totalCities} cities ({$activeCities} active, {$inactiveCities} inactive)");
        $this->command->info('Cities seeded successfully!');
    }
}
