<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Bus;

use App\DTOs\Admin\Bus\BusData;
use App\Enums\BusType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for admin bus creation.
 */
class CreateBusRequest extends FormRequest
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
            'bus_code' => ['required', 'string', 'max:255', 'unique:buses'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'type' => ['required', 'string', Rule::enum(BusType::class)],
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
