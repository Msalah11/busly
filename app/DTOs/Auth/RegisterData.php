<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Enums\Role;
use Illuminate\Http\Request;

/**
 * Data Transfer Object for user registration operations.
 */
final readonly class RegisterData
{
    /**
     * Create a new RegisterData instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public Role $role = Role::USER,
    ) {}

    /**
     * Create RegisterData from a request.
     */
    public static function fromRequest(Request $request): self
    {
        $role = Role::USER;

        // Allow role assignment if provided and user is admin
        if ($request->has('role') && $request->user()?->isAdmin()) {
            $role = Role::from($request->string('role')->toString());
        }

        return new self(
            name: $request->string('name')->toString(),
            email: strtolower($request->string('email')->toString()),
            password: $request->string('password')->toString(),
            role: $role,
        );
    }

    /**
     * Convert to array for user creation.
     *
     * @return array<string, string|Role>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ];
    }
}
