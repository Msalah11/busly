<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('reset password link screen can be displayed', function (): void {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('auth/forgot-password'));
});

test('reset password link can be requested', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHas('status');
    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password link request requires email', function (): void {
    $response = $this->post('/forgot-password', []);

    $response->assertSessionHasErrors('email');
});

test('reset password link request requires valid email format', function (): void {
    $response = $this->post('/forgot-password', [
        'email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset password link request works for non-existent email', function (): void {
    Notification::fake();

    $response = $this->post('/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    // Should still show success message for security
    $response->assertSessionHas('status');
    Notification::assertNothingSent();
});

test('reset password screen can be displayed', function (): void {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->get(sprintf('/reset-password/%s?email=%s', $token, $user->email));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('auth/reset-password')
        ->has('email')
        ->has('token')
    );
});

test('password can be reset with valid token', function (): void {
    Event::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHas('status');
    $response->assertRedirect('/login');

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();

    Event::assertDispatched(PasswordReset::class);
});

test('password reset requires token', function (): void {
    $response = $this->post('/reset-password', [
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('token');
});

test('password reset requires email', function (): void {
    $response = $this->post('/reset-password', [
        'token' => 'some-token',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password reset requires password', function (): void {
    $response = $this->post('/reset-password', [
        'token' => 'some-token',
        'email' => 'test@example.com',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password reset requires password confirmation', function (): void {
    $response = $this->post('/reset-password', [
        'token' => 'some-token',
        'email' => 'test@example.com',
        'password' => 'new-password',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password reset requires matching password confirmation', function (): void {
    $response = $this->post('/reset-password', [
        'token' => 'some-token',
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password reset requires valid email format', function (): void {
    $response = $this->post('/reset-password', [
        'token' => 'some-token',
        'email' => 'invalid-email',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password reset fails with invalid token', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password reset fails with expired token', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    // Simulate expired token by traveling forward in time
    $this->travel(61)->minutes();

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password must meet minimum requirements during reset', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertSessionHasErrors('password');
});
