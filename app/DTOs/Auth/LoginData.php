<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

/**
 * Data Transfer Object for user login operations.
 */
readonly final class LoginData
{
    /**
     * Create a new LoginData instance.
     */
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    /**
     * Create LoginData from a request.
     */
    public static function fromRequest(LoginRequest|Request $request): self
    {
        return new self(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember', false),
        );
    }

    /**
     * Convert to array for authentication credentials.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
