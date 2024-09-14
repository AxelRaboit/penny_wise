<?php

declare(strict_types=1);

namespace App\Enum;

enum MonthEnum: int
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    /**
     * Get the name of the month.
     */
    public function getName(): string
    {
        return match ($this) {
            self::January => 'January',
            self::February => 'February',
            self::March => 'March',
            self::April => 'April',
            self::May => 'May',
            self::June => 'June',
            self::July => 'July',
            self::August => 'August',
            self::September => 'September',
            self::October => 'October',
            self::November => 'November',
            self::December => 'December',
        };
    }

    /**
     * Get the list of all months as an array of MonthEnum.
     *
     * @return MonthEnum[]
     */
    public static function all(): array
    {
        return [
            self::January,
            self::February,
            self::March,
            self::April,
            self::May,
            self::June,
            self::July,
            self::August,
            self::September,
            self::October,
            self::November,
            self::December,
        ];
    }
}
