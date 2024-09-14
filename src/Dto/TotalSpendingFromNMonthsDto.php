<?php

declare(strict_types=1);

namespace App\Dto;

readonly class TotalSpendingFromNMonthsDto
{
    /**
     * @param array<array{year: int, monthNumber: int, monthName: string, total: float}> $monthlyTotals
     */
    public function __construct(private array $monthlyTotals) {}

    /**
     * @return array<array{year: int, monthNumber: int, monthName: string, total: float}>
     */
    public function getMonthlyTotals(): array
    {
        return $this->monthlyTotals;
    }
}
