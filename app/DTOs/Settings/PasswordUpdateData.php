<?php

declare(strict_types=1);

namespace App\DTOs\Settings;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for password update operations.
 */
final readonly class PasswordUpdateData
{
    /**
     * Create a new PasswordUpdateData instance.
     */
    public function __construct(
        public string $currentPassword,
        public string $password,
        public string $passwordConfirmation,
    ) {}

    /**
     * Create PasswordUpdateData from a request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            currentPassword: $request->string('current_password')->toString(),
            password: $request->string('password')->toString(),
            passwordConfirmation: $request->string('password_confirmation')->toString(),
        );
    }

    /**
     * Convert to array for validation.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'current_password' => $this->currentPassword,
            'password' => $this->password,
            'password_confirmation' => $this->passwordConfirmation,
        ];
    }
}
