<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Reservation;

use App\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request for listing reservations with filtering options.
 */
class IndexReservationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role->value === 'admin';
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
            'status' => ['nullable', 'string', Rule::enum(ReservationStatus::class)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'upcoming_only' => ['nullable', 'boolean'],
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
            'status.enum' => 'The selected status is invalid.',
            'user_id.exists' => 'The selected user does not exist.',
            'trip_id.exists' => 'The selected trip does not exist.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}