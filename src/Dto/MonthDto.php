<?php

namespace App\Dto;

readonly class MonthDto
{
    /**
     * @param int $monthNumber
     * @param string $monthName
     */
    public function __construct(private int $monthNumber, private string $monthName){}

    /**
     * @return int
     */
    public function getMonthNumber(): int
    {
        return $this->monthNumber;
    }

    /**
     * @return string
     */
    public function getMonthName(): string
    {
        return $this->monthName;
    }

}