<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TotalSpendingFromNMonthsDto;
use App\Enum\MonthEnum;
use App\Repository\WalletRepository;
use App\Util\WalletHelper;

final readonly class MonthlyFinancialDataService
{
    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private WalletRepository $walletRepository,
        private WalletHelper $walletHelper,
    ) {}

    /**
     * Calculates the total spending for the given month and the previous N months.
     *
     * @param int $year    the year of the starting month
     * @param int $month   the month number of the starting month (1-12)
     * @param int $nMonths the number of months for which to retrieve the total spending
     *
     * @return TotalSpendingFromNMonthsDto a Data Transfer Object containing totals for each month
     */
    public function getTotalSpendingForCurrentAndPreviousNthMonths(int $year, int $month, int $nMonths): TotalSpendingFromNMonthsDto
    {
        $monthsData = $this->walletHelper->getPreviousMonthsAndYears($year, $month, $nMonths);

        $totalsData = $this->walletRepository->getTotalSpendingForMonths($monthsData);

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

    /**
     * Retrieves the total spending for each year in the given range.
     *
     * @param int $startYear        the starting year of the range
     * @param int $endYear          the ending year of the range
     * @param int $incomeCategoryId the ID of the income category
     *
     * @return array<array{year: int, total: float}> an array of associative arrays, each containing the year and the total spending for that year
     */
    public function getTotalSpendingPerYear(int $startYear, int $endYear, int $incomeCategoryId): array
    {
        $results = $this->walletRepository->fetchTotalSpendingPerYear($startYear, $endYear, $incomeCategoryId);

        return array_map(static fn (array $result): array => [
            'year' => $result['year'],
            'total' => $result['total'],
        ], $results);
    }
}
