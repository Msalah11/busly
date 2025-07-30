<?php

declare(strict_types=1);

namespace App\DTOs\Settings;

use App\Enums\Role;
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
        public ?Role $role = null,
    ) {}

    /**
     * Create ProfileUpdateData from a request.
     */
    public static function fromRequest(ProfileUpdateRequest|Request $request): self
    {
        $role = null;

        // Allow role assignment if provided and user is admin
        if ($request->has('role') && $request->user()?->isAdmin()) {
            $role = Role::from($request->string('role')->toString());
        }

        return new self(
            name: $request->string('name')->toString(),
            email: strtolower($request->string('email')->toString()),
            role: $role,
        );
    }

    /**
     * Convert to array for user update.
     *
     * @return array<string, string|Role>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->role instanceof \App\Enums\Role) {
            $data['role'] = $this->role;
        }

        return $data;
    }
}
