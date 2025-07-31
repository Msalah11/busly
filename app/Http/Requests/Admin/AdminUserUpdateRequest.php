<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Settings\ProfileUpdateData;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for admin user updates.
 */
class AdminUserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Convert the request data to a ProfileUpdateData DTO.
     */
    public function toDTO(): ProfileUpdateData
    {
        return new ProfileUpdateData(
            name: (string) $this->string('name'),
            email: strtolower((string) $this->string('email')),
            role: Role::from((string) $this->string('role'))
        );
    }
}
