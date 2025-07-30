<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

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
    ) {}

    /**
     * Create RegisterData from a request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: strtolower($request->string('email')->toString()),
            password: $request->string('password')->toString(),
        );
    }

    /**
     * Convert to array for user creation.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
