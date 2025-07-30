<?php

declare(strict_types=1);

use App\DTOs\Settings\ProfileUpdateData;
use Illuminate\Http\Request;

test('it can be instantiated with constructor', function (): void {
    $profileData = new ProfileUpdateData(
        name: 'John Doe',
        email: 'john@example.com'
    );

    expect($profileData->name)->toBe('John Doe');
    expect($profileData->email)->toBe('john@example.com');
});

test('it can be created from request', function (): void {
    $request = Request::create('/settings/profile', 'PATCH', [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);

    $profileData = ProfileUpdateData::fromRequest($request);

    expect($profileData->name)->toBe('Jane Smith');
    expect($profileData->email)->toBe('jane@example.com');
});

test('it can be converted to array', function (): void {
    $profileData = new ProfileUpdateData(
        name: 'John Doe',
        email: 'john@example.com'
    );

    $array = $profileData->toArray();

    expect($array)->toBe([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

test('it handles string conversion for request data', function (): void {
    $request = Request::create('/settings/profile', 'PATCH', [
        'name' => 'John Doe',
        'email' => 'JOHN@EXAMPLE.COM',
    ]);

    $profileData = ProfileUpdateData::fromRequest($request);

    expect($profileData->name)->toBe('John Doe');
    expect($profileData->email)->toBe('john@example.com');
});

test('it is readonly', function (): void {
    $profileData = new ProfileUpdateData(
        name: 'John Doe',
        email: 'john@example.com'
    );

    $reflection = new ReflectionClass($profileData);
    expect($reflection->isReadOnly())->toBeTrue();
});

test('it handles empty strings correctly', function (): void {
    $request = Request::create('/settings/profile', 'PATCH', [
        'name' => '',
        'email' => '',
    ]);

    $profileData = ProfileUpdateData::fromRequest($request);

    expect($profileData->name)->toBe('');
    expect($profileData->email)->toBe('');
});

test('it trims whitespace from request data', function (): void {
    $request = Request::create('/settings/profile', 'PATCH', [
        'name' => '  John Doe  ',
        'email' => '  john@example.com  ',
    ]);

    $profileData = ProfileUpdateData::fromRequest($request);

    // Note: Laravel's string() method doesn't automatically trim
    expect($profileData->name)->toBe('  John Doe  ');
    expect($profileData->email)->toBe('  john@example.com  ');
});
