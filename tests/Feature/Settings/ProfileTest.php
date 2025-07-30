<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/profile');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/profile')
        ->has('user')
    );
});

test('profile information can be updated', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

    $response->assertRedirect('/settings/profile');

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is reset when email is changed', function (): void {
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is not reset when email is not changed', function (): void {
    $verifiedAt = now();
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'test@example.com',
        'email_verified_at' => $verifiedAt,
    ]);

    $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'New Name',
            'email' => 'test@example.com',
        ]);

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email_verified_at->format('Y-m-d H:i:s'))->toBe($verifiedAt->format('Y-m-d H:i:s'));
});

test('name is required for profile update', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'email' => 'test@example.com',
        ]);

    $response->assertSessionHasErrors('name');
});

test('email is required for profile update', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
        ]);

    $response->assertSessionHasErrors('email');
});

test('email must be valid email format for profile update', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);

    $response->assertSessionHasErrors('email');
});

test('email must be unique for profile update', function (): void {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create(['email' => 'user@example.com']);

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
        ]);

    $response->assertSessionHasErrors('email');
});

test('user can keep their own email during profile update', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'test@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'New Name',
            'email' => 'test@example.com',
        ]);

    $response->assertRedirect('/settings/profile');
    $response->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('test@example.com');
});

test('name cannot exceed 255 characters', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
        ]);

    $response->assertSessionHasErrors('name');
});

test('email cannot exceed 255 characters', function (): void {
    $user = User::factory()->create();
    $longEmail = str_repeat('a', 250).'@example.com'; // This creates 262 characters

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => $longEmail,
        ]);

    $response->assertSessionHasErrors('email');
});

test('user can delete their account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile', [
            'password' => 'password',
        ]);

    $response->assertRedirect('/');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('correct password must be provided to delete account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile', [
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors('password');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('password is required to delete account', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile', []);

    $response->assertSessionHasErrors('password');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('unauthenticated users cannot access profile settings', function (): void {
    $response = $this->get('/settings/profile');

    $response->assertRedirect('/login');
});

test('unauthenticated users cannot update profile', function (): void {
    $response = $this->patch('/settings/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect('/login');
});

test('unauthenticated users cannot delete account', function (): void {
    $response = $this->delete('/settings/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/login');
});
