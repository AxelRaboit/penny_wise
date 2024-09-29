<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class CalendarDto
{
    /**
     * @param YearDto[] $years
     */
    public function __construct(private array $years) {}

    /**
     * @return YearDto[]
     */
    public function getYears(): array
    {
        return $this->years;
    }
}

