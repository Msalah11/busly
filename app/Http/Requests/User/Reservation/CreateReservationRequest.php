<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Reservation;

use App\DTOs\User\Reservation\ReservationData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for creating a user reservation.
 */
class CreateReservationRequest extends FormRequest
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
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'seats_count' => ['required', 'integer', 'min:1', 'max:10'],
            'total_price' => ['required', 'numeric', 'min:0'],
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
            'trip_id.required' => 'Please select a trip.',
            'trip_id.exists' => 'The selected trip does not exist.',
            'seats_count.required' => 'Please specify the number of seats.',
            'seats_count.min' => 'At least 1 seat must be reserved.',
            'seats_count.max' => 'Maximum 10 seats can be reserved at once.',
            'total_price.required' => 'Please specify the total price.',
            'total_price.min' => 'Price cannot be negative.',
        ];
    }

    /**
     * Convert the validated request data to a ReservationData DTO.
     */
    public function toDTO(): ReservationData
    {
        return ReservationData::fromRequest($this->validated());
    }
}
