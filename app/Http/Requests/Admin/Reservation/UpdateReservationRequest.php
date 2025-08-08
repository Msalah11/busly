<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Reservation;

use App\DTOs\Admin\Reservation\ReservationData;
use App\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request for updating an existing reservation.
 */
class UpdateReservationRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'seats_count' => ['required', 'integer', 'min:1', 'max:10'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::enum(ReservationStatus::class)],
            'reserved_at' => ['nullable', 'date'],
            'cancelled_at' => ['nullable', 'date', 'after_or_equal:reserved_at'],
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
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'The selected user does not exist.',
            'trip_id.required' => 'Please select a trip.',
            'trip_id.exists' => 'The selected trip does not exist.',
            'seats_count.required' => 'Please specify the number of seats.',
            'seats_count.min' => 'At least 1 seat must be reserved.',
            'seats_count.max' => 'Maximum 10 seats can be reserved at once.',
            'total_price.required' => 'Please specify the total price.',
            'total_price.min' => 'Price cannot be negative.',
            'status.required' => 'Please select a status.',
            'status.enum' => 'The selected status is invalid.',
            'cancelled_at.after_or_equal' => 'Cancellation date must be after or equal to reservation date.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator): void {
            // Additional validation: if status is cancelled, cancelled_at should be provided
            if ($this->input('status') === ReservationStatus::CANCELLED->value && empty($this->input('cancelled_at'))) {
                $validator->errors()->add('cancelled_at', 'Cancellation date is required when status is cancelled.');
            }
        });
    }

    /**
     * Convert the validated request data to a ReservationData DTO.
     */
    public function toDTO(): ReservationData
    {
        return ReservationData::fromRequest($this->validated());
    }
}
