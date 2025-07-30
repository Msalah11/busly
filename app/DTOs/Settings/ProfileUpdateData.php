<?php

declare(strict_types=1);

namespace App\DTOs\Settings;

use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\Request;

/**
 * Data Transfer Object for profile update operations.
 */
final readonly class ProfileUpdateData
{
    /**
     * Create a new ProfileUpdateData instance.
     */
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    /**
     * Create ProfileUpdateData from a request.
     */
    public static function fromRequest(ProfileUpdateRequest|Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: strtolower($request->string('email')->toString()),
        );
    }

    /**
     * Convert to array for user update.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
