<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Reservation Status Enum
 *
 * Defines the possible states of a reservation.
 */
enum ReservationStatus: string
{
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    /**
     * Get all available statuses as an array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::CONFIRMED->value => 'Confirmed',
            self::CANCELLED->value => 'Cancelled',
        ];
    }

    /**
     * Get the display label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get the color class for the status (for UI styling).
     */
    public function colorClass(): string
    {
        return match ($this) {
            self::CONFIRMED => 'text-green-600 bg-green-100',
            self::CANCELLED => 'text-red-600 bg-red-100',
        };
    }

    /**
     * Check if the status is active (not cancelled).
     */
    public function isActive(): bool
    {
        return $this === self::CONFIRMED;
    }

    /**
     * Check if the status allows cancellation.
     */
    public function canBeCancelled(): bool
    {
        return $this === self::CONFIRMED;
    }

    /**
     * Get all statuses as options for forms.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
