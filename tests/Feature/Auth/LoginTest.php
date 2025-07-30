<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function (): void {
    RateLimiter::clear('login');
});

test('login screen can be displayed', function (): void {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('auth/login'));
});

test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Verify user was created
    expect($user->email)->toBe('test@example.com');
    expect(Hash::check('password', $user->password))->toBeTrue();

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('users can authenticate with remember me', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
    $this->assertNotNull(auth()->user()->remember_token);
});

test('users cannot authenticate with invalid password', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users cannot authenticate with invalid email', function (): void {
    $this->post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('email is required for login', function (): void {
    $response = $this->post('/login', [
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password is required for login', function (): void {
    $response = $this->post('/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHasErrors('password');
});

test('email must be valid email format', function (): void {
    $response = $this->post('/login', [
        'email' => 'invalid-email',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('login is rate limited after too many attempts', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 5 failed attempts
    for ($i = 0; $i < 5; ++$i) {
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
    }

    // 6th attempt should be rate limited
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');

    expect($response->getSession()->get('errors')->first('email'))->toContain('Too many login attempts');
});

test('successful login clears rate limit', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 4 failed attempts
    for ($i = 0; $i < 4; ++$i) {
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
    }

    // Successful login should clear rate limit
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('users can logout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
