<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

test('registration screen can be displayed', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('auth/register'));
});

test('new users can register', function (): void {
    Event::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test User');
    expect(Hash::check('password', $user->password))->toBeTrue();

    Event::assertDispatched(Registered::class);
});

test('name is required for registration', function (): void {
    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertGuest();
});

test('email is required for registration', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('password is required for registration', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('password confirmation is required', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('password must be confirmed', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('email must be valid email format', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('email must be unique', function (): void {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('name cannot exceed 255 characters', function (): void {
    $response = $this->post('/register', [
        'name' => str_repeat('a', 256),
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertGuest();
});

test('email cannot exceed 255 characters', function (): void {
    $longEmail = str_repeat('a', 250).'@example.com'; // This creates 262 characters

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => $longEmail,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('email is converted to lowercase', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->email)->toBe('test@example.com');
});

test('password must meet minimum requirements', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});
