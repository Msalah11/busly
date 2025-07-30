<?php

declare(strict_types=1);

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\RegisterData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->action = new RegisterUserAction;
});

test('it can register a new user', function (): void {
    Event::fake();

    $registerData = new RegisterData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password'
    );

    $user = $this->action->execute($registerData);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
    expect(Hash::check('password', $user->password))->toBeTrue();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

test('it dispatches registered event', function (): void {
    Event::fake();

    $registerData = new RegisterData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password'
    );

    $user = $this->action->execute($registerData);

    Event::assertDispatched(Registered::class, fn ($event): bool => $event->user->id === $user->id);
});

test('it logs in the user after registration', function (): void {
    $registerData = new RegisterData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password'
    );

    $user = $this->action->execute($registerData);

    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->id)->toBe($user->id);
});

test('it hashes the password', function (): void {
    $registerData = new RegisterData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'plain-password'
    );

    $user = $this->action->execute($registerData);

    expect($user->password)->not->toBe('plain-password');
    expect(Hash::check('plain-password', $user->password))->toBeTrue();
});

test('it returns the created user', function (): void {
    $registerData = new RegisterData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password'
    );

    $user = $this->action->execute($registerData);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->exists)->toBeTrue();
    expect($user->wasRecentlyCreated)->toBeTrue();
});

test('it creates user with correct attributes', function (): void {
    $registerData = new RegisterData(
        name: 'Jane Smith',
        email: 'jane.smith@example.com',
        password: 'secret123'
    );

    $user = $this->action->execute($registerData);

    expect($user->name)->toBe('Jane Smith');
    expect($user->email)->toBe('jane.smith@example.com');
    expect($user->email_verified_at)->toBeNull();
    expect($user->created_at)->not->toBeNull();
    expect($user->updated_at)->not->toBeNull();
});
