<?php

declare(strict_types=1);

use App\Actions\Auth\LoginUserAction;
use App\DTOs\Auth\LoginData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->action = new LoginUserAction;
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    RateLimiter::clear('test@example.com|127.0.0.1');
});

test('it can authenticate user with valid credentials', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'password',
        remember: false
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    $request->setLaravelSession(app('session.store'));

    $this->action->execute($loginData, $request);

    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->email)->toBe('test@example.com');
});

test('it can authenticate user with remember me', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'password',
        remember: true
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'password',
        'remember' => true,
    ]);
    $request->setLaravelSession(app('session.store'));

    $this->action->execute($loginData, $request);

    expect(Auth::check())->toBeTrue();
    // The user should have a remember token set
    expect(Auth::user()->remember_token)->not->toBeNull();
});

test('it throws validation exception for invalid credentials', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'wrong-password',
        remember: false
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);
    $request->setLaravelSession(app('session.store'));

    expect(fn () => $this->action->execute($loginData, $request))
        ->toThrow(ValidationException::class);

    expect(Auth::check())->toBeFalse();
});

test('it throws validation exception for non-existent user', function (): void {
    $loginData = new LoginData(
        email: 'nonexistent@example.com',
        password: 'password',
        remember: false
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);
    $request->setLaravelSession(app('session.store'));

    expect(fn () => $this->action->execute($loginData, $request))
        ->toThrow(ValidationException::class);

    expect(Auth::check())->toBeFalse();
});

test('it regenerates session on successful login', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'password',
        remember: false
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    $request->setLaravelSession(app('session.store'));

    $originalSessionId = $request->session()->getId();

    $this->action->execute($loginData, $request);

    expect($request->session()->getId())->not->toBe($originalSessionId);
});

test('it increments rate limiter on failed attempts', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'wrong-password',
        remember: false
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);
    $request->setLaravelSession(app('session.store'));

    try {
        $this->action->execute($loginData, $request);
    } catch (ValidationException) {
        // Expected
    }

    expect(RateLimiter::attempts('test@example.com|127.0.0.1'))->toBe(1);
});

test('it clears rate limiter on successful login', function (): void {
    // First, make a failed attempt
    $failedLoginData = new LoginData(
        email: 'test@example.com',
        password: 'wrong-password',
        remember: false
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);
    $request->setLaravelSession(app('session.store'));

    try {
        $this->action->execute($failedLoginData, $request);
    } catch (ValidationException) {
        // Expected
    }

    expect(RateLimiter::attempts('test@example.com|127.0.0.1'))->toBe(1);

    // Now make a successful attempt
    $successfulLoginData = new LoginData(
        email: 'test@example.com',
        password: 'password',
        remember: false
    );

    $this->action->execute($successfulLoginData, $request);

    expect(RateLimiter::attempts('test@example.com|127.0.0.1'))->toBe(0);
});

test('it throws validation exception when rate limited', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'wrong-password',
        remember: false
    );

    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);
    $request->setLaravelSession(app('session.store'));

    // Make 5 failed attempts to trigger rate limiting
    for ($i = 0; $i < 5; ++$i) {
        try {
            $this->action->execute($loginData, $request);
        } catch (ValidationException) {
            // Expected
        }
    }

    // 6th attempt should be rate limited
    expect(fn () => $this->action->execute($loginData, $request))
        ->toThrow(ValidationException::class);
});
