<?php

namespace App\Dto;

readonly class MonthDto
{
    public function __construct(private int $monthNumber, private string $monthName){}

    public function getMonthNumber(): int
    {
        return $this->monthNumber;
    }

    public function getMonthName(): string
    {
        return $this->monthName;
    }

}