<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for password reset operations.
 */
final readonly class PasswordResetData
{
    /**
     * Create a new PasswordResetData instance.
     */
    public function __construct(
        public string $email,
        public string $password,
        public string $passwordConfirmation,
        public string $token,
    ) {}

    /**
     * Create PasswordResetData from a request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            passwordConfirmation: $request->string('password_confirmation')->toString(),
            token: $request->string('token')->toString(),
        );
    }

    /**
     * Convert to array for password reset.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->passwordConfirmation,
            'token' => $this->token,
        ];
    }
}
