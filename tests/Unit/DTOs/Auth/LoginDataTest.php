<?php

declare(strict_types=1);

use App\DTOs\Auth\LoginData;
use Illuminate\Http\Request;

test('it can be instantiated with constructor', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'password',
        remember: true
    );

    expect($loginData->email)->toBe('test@example.com');
    expect($loginData->password)->toBe('password');
    expect($loginData->remember)->toBeTrue();
});

test('it has default value for remember parameter', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'password'
    );

    expect($loginData->remember)->toBeFalse();
});

test('it can be created from request', function (): void {
    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'secret',
        'remember' => true,
    ]);

    $loginData = LoginData::fromRequest($request);

    expect($loginData->email)->toBe('test@example.com');
    expect($loginData->password)->toBe('secret');
    expect($loginData->remember)->toBeTrue();
});

test('it can be created from request without remember', function (): void {
    $request = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'secret',
    ]);

    $loginData = LoginData::fromRequest($request);

    expect($loginData->email)->toBe('test@example.com');
    expect($loginData->password)->toBe('secret');
    expect($loginData->remember)->toBeFalse();
});

test('it can be converted to array', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'password',
        remember: true
    );

    $array = $loginData->toArray();

    expect($array)->toBe([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
});

test('it handles string conversion for request data', function (): void {
    $request = Request::create('/login', 'POST', [
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password123',
        'remember' => '1',
    ]);

    $loginData = LoginData::fromRequest($request);

    expect($loginData->email)->toBe('TEST@EXAMPLE.COM');
    expect($loginData->password)->toBe('password123');
    expect($loginData->remember)->toBeTrue();
});

test('it is readonly', function (): void {
    $loginData = new LoginData(
        email: 'test@example.com',
        password: 'password'
    );

    $reflection = new ReflectionClass($loginData);
    expect($reflection->isReadOnly())->toBeTrue();
});

test('it handles boolean conversion correctly', function (): void {
    $request1 = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'password',
        'remember' => 'on',
    ]);

    $request2 = Request::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'password',
        'remember' => '0',
    ]);

    $loginData1 = LoginData::fromRequest($request1);
    $loginData2 = LoginData::fromRequest($request2);

    expect($loginData1->remember)->toBeTrue();
    expect($loginData2->remember)->toBeFalse();
});
