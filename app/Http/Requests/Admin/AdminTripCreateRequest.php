<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Admin\TripData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for admin trip creation.
 */
class AdminTripCreateRequest extends FormRequest
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
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255', 'different:origin'],
            'departure_time' => ['required', 'date', 'after:now'],
            'arrival_time' => ['required', 'date', 'after:departure_time'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Convert the request data to a TripData DTO.
     */
    public function toDTO(): TripData
    {
        return TripData::fromRequest($this);
    }
}
