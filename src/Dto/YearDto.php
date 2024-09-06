<?php

namespace App\Dto;

readonly class YearDto
{
    /**
     * @param int $year
     * @param MonthDto[] $months  An array of MonthDto objects
     */
    public function __construct(private int $year, private array $months){}

    /**
     * @return int
     */
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