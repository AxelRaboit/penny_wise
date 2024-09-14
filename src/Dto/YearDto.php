<?php

declare(strict_types=1);

namespace App\Dto;

readonly class YearDto
{
    /**
     * @param MonthDto[] $months An array of MonthDto objects
     */
    public function __construct(private int $year, private array $months) {}

    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @return MonthDto[]
     */
    public function getMonths(): array
    {
        return $this->months;
    }
}
