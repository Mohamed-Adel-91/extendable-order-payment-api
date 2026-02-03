<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaymentStatus extends Enum
{
    public const PENDING   = 1;
    public const SUCCESS   = 2;
    public const FAILED    = 3;
    public const CANCELLED = 4;
    public const REFUNDED  = 5;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::PENDING   => 'Pending',
            self::SUCCESS   => 'Success',
            self::FAILED    => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED  => 'Refunded',
            default         => parent::getDescription($value),
        };
    }
}
