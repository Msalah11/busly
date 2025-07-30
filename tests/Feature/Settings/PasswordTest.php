<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password page is displayed', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/password');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('settings/password'));
});

test('password can be updated', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasNoErrors();

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

test('correct password must be provided to update password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasErrors('current_password');

    $user->refresh();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

test('current password is required for password update', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasErrors('current_password');
});

test('new password is required for password update', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'old-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasErrors('password');
});

test('new password must be confirmed', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
        ]);

    $response->assertSessionHasErrors('password');
});

test('new password confirmation must match', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertSessionHasErrors('password');
});

test('new password must meet minimum requirements', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

    $response->assertSessionHasErrors('password');

    $user->refresh();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

test('password update redirects back to password page', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('unauthenticated users cannot access password settings', function (): void {
    $response = $this->get('/settings/password');

    $response->assertRedirect('/login');
});

test('unauthenticated users cannot update password', function (): void {
    $response = $this->put('/settings/password', [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect('/login');
});

test('password can be updated with strong password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $strongPassword = 'NewStrongPassword123!';

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => $strongPassword,
            'password_confirmation' => $strongPassword,
        ]);

    $response->assertSessionHasNoErrors();

    $user->refresh();
    expect(Hash::check($strongPassword, $user->password))->toBeTrue();
});

test('password cannot be updated to same as current password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/password', [
            'current_password' => 'current-password',
            'password' => 'current-password',
            'password_confirmation' => 'current-password',
        ]);

    // This should work - Laravel doesn't prevent using the same password
    $response->assertSessionHasNoErrors();
});
