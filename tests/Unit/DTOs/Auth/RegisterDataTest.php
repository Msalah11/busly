<?php

declare(strict_types=1);

use App\DTOs\Auth\RegisterData;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;

describe('RegisterData DTO', function (): void {
    it('creates from request with default user role', function (): void {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'JOHN@EXAMPLE.COM',
            'password' => 'password123',
        ]);

        $dto = RegisterData::fromRequest($request);

        expect($dto->name)->toBe('John Doe');
        expect($dto->email)->toBe('john@example.com');
        expect($dto->password)->toBe('password123');
        expect($dto->role)->toBe(Role::USER);
    });

    it('allows admin to assign roles during registration', function (): void {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $request = Request::create('/', 'POST', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $request->setUserResolver(fn () => $admin);

        $dto = RegisterData::fromRequest($request);

        expect($dto->role)->toBe(Role::ADMIN);
    });

    it('ignores role assignment for non-admin users', function (): void {
        $user = User::factory()->create(['role' => Role::USER]);

        $request = Request::create('/', 'POST', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $request->setUserResolver(fn () => $user);

        $dto = RegisterData::fromRequest($request);

        expect($dto->role)->toBe(Role::USER);
    });

    it('converts to array including role', function (): void {
        $dto = new RegisterData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
            role: Role::ADMIN
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => Role::ADMIN,
        ]);
    });

    it('handles string conversion for request data', function (): void {
        $request = Request::create('/', 'POST', [
            'name' => 123,
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 456,
        ]);

        $dto = RegisterData::fromRequest($request);

        expect($dto->name)->toBe('123');
        expect($dto->email)->toBe('test@example.com');
        expect($dto->password)->toBe('456');
        expect($dto->role)->toBe(Role::USER);
    });
});
