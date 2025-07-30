<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Bus Type Enum
 *
 * Defines the different types of buses available in the system.
 */
enum BusType: string
{
    case STANDARD = 'Standard';
    case VIP = 'VIP';

    /**
     * Get all available bus types as an array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::STANDARD->value => 'Standard',
            self::VIP->value => 'VIP',
        ];
    }

    /**
     * Get the display label for the bus type.
     */
    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard Bus',
            self::VIP => 'VIP Bus',
        };
    }

    /**
     * Get the description for the bus type.
     */
    public function description(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard comfort bus with basic amenities',
            self::VIP => 'VIP bus with premium comfort and enhanced amenities',
        };
    }

    /**
     * Get all bus types as options for forms.
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
