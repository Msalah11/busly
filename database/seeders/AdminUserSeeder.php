<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@busly.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@busly.com',
                'password' => Hash::make('password'),
                'role' => Role::ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Create a regular user for testing
        User::firstOrCreate(
            ['email' => 'user@busly.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@busly.com',
                'password' => Hash::make('password'),
                'role' => Role::USER,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created: admin@busly.com (password: password)');
        $this->command->info('Regular user created: user@busly.com (password: password)');
    }
}
