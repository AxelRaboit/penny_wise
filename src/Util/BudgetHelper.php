<?php

namespace App\Util;

class BudgetHelper
{
    private const int MONTH_JANUARY = 1;

    private const int MONTH_DECEMBER = 12;

    /**
     * Returns the previous month and year given a specific month and year.
     *
     * @return array<string, int>
     */
    public static function getPreviousMonthAndYear(int $year, int $month): array
    {
        if ($month === self::MONTH_JANUARY) {
            return ['year' => $year - 1, 'month' => self::MONTH_DECEMBER];
        }

        return ['year' => $year, 'month' => $month - 1];
    }
}
