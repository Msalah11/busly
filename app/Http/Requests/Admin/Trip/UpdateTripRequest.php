<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Trip;

use App\DTOs\Admin\Trip\TripData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for admin trip updates.
 */
class UpdateTripRequest extends FormRequest
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
            'departure_time' => ['required', 'date_format:H:i'],
            'arrival_time' => ['required', 'date_format:H:i'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator): void {
            $departureTime = $this->input('departure_time');
            $arrivalTime = $this->input('arrival_time');

            if ($departureTime && $arrivalTime) {
                try {
                    $departure = \Carbon\Carbon::createFromFormat('H:i', $departureTime);
                    $arrival = \Carbon\Carbon::createFromFormat('H:i', $arrivalTime);

                    if ($arrival->lte($departure)) {
                        $validator->errors()->add('arrival_time', 'The arrival time must be after the departure time.');
                    }
                } catch (\Carbon\Exceptions\InvalidFormatException) {
                    // Time format validation is already handled by the 'date_format:H:i' rule
                }
            }
        });
    }

    /**
     * Convert the request data to a TripData DTO.
     */
    public function toDTO(): TripData
    {
        return TripData::fromRequest($this);
    }
}
