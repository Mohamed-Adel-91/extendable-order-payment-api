<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaymentMethod extends Enum
{
    public const KASHIER = 1;
    public const FAWRY   = 2;
    public const VALU    = 3;
    public const PAYMOB    = 4;
    public const VODAFONE_WALLET    = 5;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::KASHIER          => 'Kashier',
            self::FAWRY            => 'Fawry',
            self::VALU             => 'ValU',
            self::PAYMOB           => 'Paymob',
            self::VODAFONE_WALLET  => 'Vodafone Wallet',
            default                => parent::getDescription($value),
        };
    }


}
