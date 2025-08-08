<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\City;

use App\DTOs\Admin\City\CityData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for creating a new city.
 */
class CreateCityRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:cities,name'],
            'code' => ['required', 'string', 'max:10', 'unique:cities,code'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
            'name.required' => 'Please enter the city name.',
            'name.unique' => 'A city with this name already exists.',
            'code.required' => 'Please enter the city code.',
            'code.unique' => 'A city with this code already exists.',
            'code.max' => 'City code cannot be longer than 10 characters.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'sort_order.min' => 'Sort order cannot be negative.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->input('code')),
            ]);
        }

        // Set default values
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        if (!$this->has('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }

    /**
     * Convert the validated request data to a CityData DTO.
     */
    public function toDTO(): CityData
    {
        return CityData::fromRequest($this->validated());
    }
}
