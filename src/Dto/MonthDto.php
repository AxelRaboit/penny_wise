<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class MonthDto
{
    public function __construct(private int $monthNumber, private string $monthName, private int $walletId) {}

    public function getMonthNumber(): int
    {
        return $this->monthNumber;
    }

    public function getMonthName(): string
    {
        return $this->monthName;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }
}
