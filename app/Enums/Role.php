<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * User Role Enum
 *
 * Defines the different roles available in the system.
 */
enum Role: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Get all available roles as an array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::USER->value => 'User',
        ];
    }

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'User',
        };
    }

    /**
     * Get the description for the role.
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full system access with administrative privileges',
            self::USER => 'Standard user with basic system access',
        };
    }

    /**
     * Check if the role has admin privileges.
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if the role is a standard user.
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }

    /**
     * Get all roles as options for forms.
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

    /**
     * Get the default role for new users.
     */
    public static function default(): self
    {
        return self::USER;
    }
}
