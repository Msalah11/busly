<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\DTOs\Auth\RegisterData;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for admin user creation.
 */
class CreateUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
        ];
    }

    /**
     * Convert the request data to a RegisterData DTO.
     */
    public function toDTO(): RegisterData
    {
        return new RegisterData(
            name: (string) $this->string('name'),
            email: strtolower((string) $this->string('email')),
            password: (string) $this->string('password'),
            role: Role::from((string) $this->string('role'))
        );
    }
}
