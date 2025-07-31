<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;

describe('Admin User Creation', function (): void {
    it('does not auto-login admin when creating a new user', function (): void {
        // Create an admin user
        $admin = User::factory()->admin()->create();

        // Login as admin
        $this->actingAs($admin);

        // Verify we're logged in as admin
        expect(auth()->user()->id)->toBe($admin->id);
        expect(auth()->user()->role)->toBe(Role::ADMIN);

        // Create a new user via admin interface
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ];

        $response = $this->post(route('admin.users.store'), $userData);

        // Should redirect to users index
        $response->assertRedirect(route('admin.users.index'));

        // Admin should still be logged in (not the new user)
        expect(auth()->user()->id)->toBe($admin->id);
        expect(auth()->user()->role)->toBe(Role::ADMIN);
        expect(auth()->user()->email)->toBe($admin->email);

        // Verify the new user was created but is not logged in
        $newUser = User::where('email', 'test@example.com')->first();
        expect($newUser)->not->toBeNull();
        expect($newUser->name)->toBe('Test User');
        expect($newUser->role)->toBe(Role::USER);
        expect($newUser->id)->not->toBe(auth()->user()->id);
    });

    it('auto-logs in user during normal registration', function (): void {
        // Test normal registration still works
        $userData = [
            'name' => 'Normal User',
            'email' => 'normal@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // Should redirect to dashboard
        $response->assertRedirect('/dashboard');

        // User should be logged in
        expect(auth()->check())->toBeTrue();
        expect(auth()->user()->email)->toBe('normal@example.com');
        expect(auth()->user()->name)->toBe('Normal User');
    });
});
