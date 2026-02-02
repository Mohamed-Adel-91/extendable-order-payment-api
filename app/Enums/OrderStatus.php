<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class OrderStatus extends Enum
{
    public const CREATED   = 1;
    public const PAID      = 2;
    public const SHIPPING  = 3;
    public const DELIVERED = 4;
    public const CANCELLED = 5;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::CREATED   => 'Order created',
            self::PAID      => 'Payment completed successfully',
            self::SHIPPING  => 'Shipping order',
            self::DELIVERED => 'Order delivered',
            self::CANCELLED => 'Order cancelled',
            default         => 'Unknown status',
        };
    }
}
