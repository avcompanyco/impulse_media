<?php

namespace App\Enums\User;

enum UserStatusEnum: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    /**
     * Get all status values
     */
    public static function values(): array
    {
        return [
            self::ACTIVE->value,
            self::SUSPENDED->value,
        ];
    }

    /**
     * Get status labels for display
     */
    public static function labels(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::SUSPENDED->value => 'Suspended',
        ];
    }

    /**
     * Get label for current status
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }
}