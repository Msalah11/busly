<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Bus;

use App\DTOs\Admin\Bus\BusData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for admin bus updates.
 */
class UpdateBusRequest extends FormRequest
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
        $busId = $this->route('bus')?->id;

        return [
            'bus_code' => ['required', 'string', 'max:255', Rule::unique('buses')->ignore($busId)],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'type' => ['required', 'string', Rule::in(['Standard', 'VIP'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Convert the request data to a BusData DTO.
     */
    public function toDTO(): BusData
    {
        return BusData::fromRequest($this);
    }
}
