<?php

namespace App\Util;

use App\Enum\MonthEnum;

class BudgetHelper
{
    /**
     * Returns the previous month and year given a specific month and year.
     *
     * @return array<string, int>
     */
    public static function getPreviousMonthAndYear(int $year, int $month): array
    {
        $currentMonthEnum = MonthEnum::from($month);

        if ($currentMonthEnum === MonthEnum::January) {
            return ['year' => $year - 1, 'month' => MonthEnum::December->value];
        }

        return ['year' => $year, 'month' => $currentMonthEnum->value - 1];
    }

    /**
     * Returns the next month and year given a specific month and year.
     *
     * @return array<string, int>
     */
    public static function getNextMonthAndYear(int $year, int $month): array
    {
        $currentMonthEnum = MonthEnum::from($month);

        if ($currentMonthEnum === MonthEnum::December) {
            return ['year' => $year + 1, 'month' => MonthEnum::January->value];
        }

        return ['year' => $year, 'month' => $currentMonthEnum->value + 1];
    }
}
