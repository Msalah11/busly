<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request for searching trips by users.
 */
class SearchTripsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'origin_city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'destination_city_id' => ['nullable', 'integer', 'exists:cities,id', 'different:origin_city_id'],
            'departure_date' => ['nullable', 'date', 'after_or_equal:today'],
            'min_seats' => ['nullable', 'integer', 'min:1', 'max:50'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'sort_by' => ['nullable', 'string', Rule::in(['departure_time', 'price', 'available_seats'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
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
            'origin_city_id.exists' => 'The selected origin city does not exist.',
            'destination_city_id.exists' => 'The selected destination city does not exist.',
            'destination_city_id.different' => 'Destination city must be different from origin city.',
            'departure_date.after_or_equal' => 'Departure date must be today or later.',
            'min_seats.min' => 'Minimum seats must be at least 1.',
            'min_seats.max' => 'Minimum seats cannot exceed 50.',
            'max_price.min' => 'Maximum price cannot be negative.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 50.',
            'sort_by.in' => 'Invalid sort field.',
            'sort_direction.in' => 'Sort direction must be asc or desc.',
        ];
    }
}
