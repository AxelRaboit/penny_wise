<?php

declare(strict_types=1);

namespace App\Service\Account\Wallet;

use App\Dto\Wallet\TotalSpendingFromNMonthsDto;
use App\Enum\Wallet\MonthEnum;
use App\Repository\Wallet\WalletRepository;
use App\Util\WalletHelper;

final readonly class MonthlyFinancialDataService
{
    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private WalletRepository $walletRepository,
        private WalletHelper $walletHelper,
    ) {}

    public function getTotalSpendingForCurrentAndPreviousNthMonths(int $accountId, int $year, int $month, int $nMonths): TotalSpendingFromNMonthsDto
    {
        $monthsData = $this->walletHelper->getPreviousMonthsAndYears($year, $month, $nMonths, $accountId);

        $totalsData = $this->walletRepository->getTotalSpendingForMonths($monthsData);

        usort($totalsData, function ($a, $b): int {
            if ($a['year'] === $b['year']) {
                return $a['month'] <=> $b['month'];
            }

            return $a['year'] <=> $b['year'];
        });

        $totals = [];

        foreach ($totalsData as $totalData) {
            $monthEnum = MonthEnum::from($totalData['month']);
            $totals[] = [
                'year' => $totalData['year'],
                'monthNumber' => $totalData['month'],
                'monthName' => $monthEnum->getName(),
                'total' => $totalData['total'],
                'totalSaving' => self::DEFAULT_BALANCE,
            ];
        }

        return new TotalSpendingFromNMonthsDto($totals);
    }
}
