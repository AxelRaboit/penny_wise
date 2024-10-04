<?php

declare(strict_types=1);

namespace App\Dto\Wallet;

final readonly class MonthDto
{
    public function __construct(
        public int $monthNumber,
        public string $monthName,
        public int $walletId
    ) {}

    /**
     * Creates a MonthDto from an array of data.
     *
     * @param array{month: int, month_name: string, wallet_id: int} $data
     */
    public static function createFrom(array $data): self
    {
        return new self(
            monthNumber: $data['month'],
            monthName: $data['month_name'],
            walletId: $data['wallet_id']
        );
    }

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
