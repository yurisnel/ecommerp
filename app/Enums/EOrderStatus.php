<?php

namespace App\Enums;

class EOrderStatus
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const PROCESSING = 'processing';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    public static function all(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PROCESSING,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
        ];
    }

    public static function canTransitionFrom(string $currentStatus): array
    {
        return match ($currentStatus) {
            self::PENDING => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED, self::CANCELLED],
            self::DELIVERED => [],
            self::CANCELLED => [],
            default => [],
        };
    }

    /**
     * Check if transition from one status to another is valid
     * 
     * @param string $from Current status
     * @param string $to Target status
     * @return bool
     */
    public static function isValidTransition(string $from = '', string $to = ''): bool
    {
        $allowedTransitions = self::canTransitionFrom($from);
        return in_array($to, $allowedTransitions);
    }

    /**
     * Get all valid next statuses from current status
     * 
     * @param string $currentStatus
     * @return array
     */
    public static function getValidTransitions(string $currentStatus): array
    {
        return self::canTransitionFrom($currentStatus);
    }
}
