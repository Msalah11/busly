<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\DTOs\Admin\User\AdminUserListData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for admin users listing.
 */
class IndexUserRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Convert the request to AdminUserListData DTO.
     */
    public function toDTO(): AdminUserListData
    {
        return AdminUserListData::fromRequest($this);
    }
}
