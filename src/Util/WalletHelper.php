<?php

declare(strict_types=1);

namespace App\Util;

use App\Enum\MonthEnum;

class WalletHelper
{
    /**
     * Returns the immediate previous month and year based on the current month, without checking for any constraints.
     *
     * @return array<string, int>
     */
    public static function getImmediatePreviousMonthAndYear(int $year, int $month): array
    {
        $currentMonthEnum = MonthEnum::from($month);

        if (MonthEnum::January === $currentMonthEnum) {
            return ['year' => $year - 1, 'month' => MonthEnum::December->value];
        }

        return ['year' => $year, 'month' => $currentMonthEnum->value - 1];
    }

    /**
     * Finds the previous valid month and year numbers by decrementing the month and wrapping around the year if necessary.
     *
     * @param int $year  the current year
     * @param int $month the current month
     *
     * @return array{year: int, month: int} an array with keys 'year' and 'month'
     */
    public function findPreviousValidMonthAndYear(int $year, int $month): array
    {
        if (0 === --$month) {
            $month = MonthEnum::December->value;
            --$year;
        }

        return ['year' => $year, 'month' => $month];
    }

    /**
     * Returns the next month and year given a specific month and year.
     *
     * @return array<string, int>
     */
    public static function getNextMonthAndYear(int $year, int $month): array
    {
        $currentMonthEnum = MonthEnum::from($month);

        if (MonthEnum::December === $currentMonthEnum) {
            return ['year' => $year + 1, 'month' => MonthEnum::January->value];
        }

        return ['year' => $year, 'month' => $currentMonthEnum->value + 1];
    }
}
