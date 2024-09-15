<?php

declare(strict_types=1);

namespace App\Enum;

enum CurrencyEnum: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case CAD = 'CAD';
    case GBP = 'GBP';
    case AUD = 'AUD';
    case JPY = 'JPY';
    case CHF = 'CHF';
    case CNY = 'CNY';
    case NZD = 'NZD';
    case INR = 'INR';
    case RUB = 'RUB';
    case ZAR = 'ZAR';
    case MXN = 'MXN';
    case BRL = 'BRL';
    case SGD = 'SGD';
    case TWD = 'TWD';

    public function getLabel(): string
    {
        return match ($this) {
            self::EUR => 'Euro',
            self::USD => 'US Dollar',
            self::CAD => 'Canadian Dollar',
            self::GBP => 'British Pound',
            self::AUD => 'Australian Dollar',
            self::JPY => 'Japanese Yen',
            self::CHF => 'Swiss Franc',
            self::CNY => 'Chinese Yuan',
            self::NZD => 'New Zealand Dollar',
            self::INR => 'Indian Rupee',
            self::RUB => 'Russian Ruble',
            self::ZAR => 'South African Rand',
            self::MXN => 'Mexican Peso',
            self::BRL => 'Brazilian Real',
            self::SGD => 'Singapore Dollar',
            self::TWD => 'New Taiwan Dollar',
        };
    }

    public static function all(): array
    {
        return [
            self::EUR,
            self::USD,
            self::CAD,
            self::GBP,
            self::AUD,
            self::JPY,
            self::CHF,
            self::CNY,
            self::NZD,
            self::INR,
            self::RUB,
            self::ZAR,
            self::MXN,
            self::BRL,
            self::SGD,
            self::TWD,
        ];
    }
}
