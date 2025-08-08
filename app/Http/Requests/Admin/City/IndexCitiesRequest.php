<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\City;

use App\DTOs\Admin\City\AdminCityListData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for listing cities with filtering options.
 */
class IndexCitiesRequest extends FormRequest
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
            'is_active' => ['nullable', 'string', 'in:true,false,all'],
            'sort_by' => ['nullable', 'string', 'in:name,code,sort_order,created_at'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'is_active.in' => 'The active filter must be true, false, or all.',
            'sort_by.in' => 'The sort field is invalid.',
            'sort_direction.in' => 'The sort direction must be asc or desc.',
        ];
    }

    /**
     * Convert the validated request data to an AdminCityListData DTO.
     */
    public function toDTO(): AdminCityListData
    {
        return AdminCityListData::fromRequest($this->validated());
    }
}
