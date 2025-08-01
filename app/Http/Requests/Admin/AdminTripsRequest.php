<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Admin\AdminTripListData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for admin trip listing.
 */
class AdminTripsRequest extends FormRequest
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
            'bus_id' => ['nullable', 'integer', 'exists:buses,id'],
            'active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Convert the request data to an AdminTripListData DTO.
     */
    public function toDTO(): AdminTripListData
    {
        return AdminTripListData::fromRequest($this);
    }
}
