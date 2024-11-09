<?php

namespace App\Constants;

class CurrencyConstants
{
    public const USD = [
        'code' => 'USD',
        'to_PEN' => 3.70,
    ];

    public const PEN = [
        'code' => 'PEN',
        'to_USD' => 0.27,
    ];

    public static function getCurrency(string $currency): array
    {
        return match ($currency) {
            'USD' => self::USD,
            'PEN' => self::PEN,
            default => [],
        };
    }

}
