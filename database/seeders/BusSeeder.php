<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BusType;
use App\Models\Bus;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a mix of Standard and VIP buses with realistic bus codes
        $buses = [
            // Standard buses
            ['bus_code' => 'ST001', 'capacity' => 45, 'type' => BusType::STANDARD, 'is_active' => true],
            ['bus_code' => 'ST002', 'capacity' => 50, 'type' => BusType::STANDARD, 'is_active' => true],
            ['bus_code' => 'ST003', 'capacity' => 42, 'type' => BusType::STANDARD, 'is_active' => true],
            ['bus_code' => 'ST004', 'capacity' => 48, 'type' => BusType::STANDARD, 'is_active' => true],
            ['bus_code' => 'ST005', 'capacity' => 44, 'type' => BusType::STANDARD, 'is_active' => false], // Inactive for maintenance
            ['bus_code' => 'ST006', 'capacity' => 46, 'type' => BusType::STANDARD, 'is_active' => true],
            ['bus_code' => 'ST007', 'capacity' => 52, 'type' => BusType::STANDARD, 'is_active' => true],
            ['bus_code' => 'ST008', 'capacity' => 40, 'type' => BusType::STANDARD, 'is_active' => true],

            // VIP buses
            ['bus_code' => 'VIP01', 'capacity' => 28, 'type' => BusType::VIP, 'is_active' => true],
            ['bus_code' => 'VIP02', 'capacity' => 32, 'type' => BusType::VIP, 'is_active' => true],
            ['bus_code' => 'VIP03', 'capacity' => 30, 'type' => BusType::VIP, 'is_active' => true],
            ['bus_code' => 'VIP04', 'capacity' => 24, 'type' => BusType::VIP, 'is_active' => false], // Inactive for maintenance
            ['bus_code' => 'VIP05', 'capacity' => 36, 'type' => BusType::VIP, 'is_active' => true],
            ['bus_code' => 'VIP06', 'capacity' => 26, 'type' => BusType::VIP, 'is_active' => true],
        ];

        foreach ($buses as $busData) {
            Bus::create($busData);
        }

        // Create additional random buses using factory
        Bus::factory()->standard()->count(5)->create();
        Bus::factory()->vip()->count(3)->create();

        // Create some inactive buses
        Bus::factory()->inactive()->count(2)->create();
    }
}
