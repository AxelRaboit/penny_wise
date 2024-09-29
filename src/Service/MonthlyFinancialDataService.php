<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TotalSpendingFromNMonthsDto;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Repository\WalletRepository;
use App\Util\WalletHelper;

final readonly class MonthlyFinancialDataService
{
    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private WalletRepository $walletRepository,
        private TransactionService $transactionService,
        private WalletHelper $walletHelper,
    ) {}

    /**
     * Calculates the total saving and spending for a given user over the current and previous N months.
     *
     * @param User $user    the user for whom to calculate savings and spending
     * @param int  $year    the starting year for the calculation
     * @param int  $month   the starting month for the calculation
     * @param int  $nMonths the number of months to include in the calculation
     *
     * @return TotalSpendingFromNMonthsDto an object containing the total spending and saving information for the requested months
     */
    public function getTotalSavingForCurrentAndPreviousNthMonths(User $user, int $year, int $month, int $nMonths): TotalSpendingFromNMonthsDto
    {
        $totals = [];

        for ($i = 0; $i < $nMonths; ++$i) {
            $monthEnum = MonthEnum::from($month);

            $wallet = $this->walletRepository->findWalletByUser($user, $year, $month);
            if ($wallet instanceof Wallet) {
                $transactions = $this->transactionService->getAllTransactionInformationByUser($wallet);
                $totalSaving = $transactions->getTotalSaving();
                $totalSpending = $transactions->getTotalSpending();
            } else {
                $totalSaving = self::DEFAULT_BALANCE;
                $totalSpending = self::DEFAULT_BALANCE;
            }

            $totals[] = [
                'year' => $year,
                'monthNumber' => $month,
                'monthName' => $monthEnum->getName(),
                'total' => $totalSpending,
                'totalSaving' => $totalSaving,
            ];

            $previousMonth = $this->walletHelper->getImmediatePreviousMonthAndYear($year, $month);
            $year = $previousMonth['year'];
            $month = $previousMonth['month'];
        }

        return new TotalSpendingFromNMonthsDto($totals);
    }

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
        $totals = [];

        for ($i = 0; $i < $nMonths; ++$i) {
            $monthEnum = MonthEnum::from($month);

            $totals[] = [
                'year' => $year,
                'monthNumber' => $month,
                'monthName' => $monthEnum->getName(),
                'total' => $this->walletRepository->getTotalSpendingByMonth($year, $month),
                'totalSaving' => self::DEFAULT_BALANCE,
            ];

            $previousMonth = $this->walletHelper->getImmediatePreviousMonthAndYear($year, $month);
            $year = $previousMonth['year'];
            $month = $previousMonth['month'];
        }

        return new TotalSpendingFromNMonthsDto($totals);
    }

    /**
     * Retrieves the total spending for each year in the given range.
     *
     * @param int $startYear the starting year of the range
     * @param int $endYear   the ending year of the range
     *
     * @return array<array{year: int, total: float}> an array of associative arrays, each containing the year and the total spending for that year
     */
    public function getTotalSpendingPerYear(int $startYear, int $endYear): array
    {
        $results = $this->walletRepository->fetchTotalSpendingPerYear($startYear, $endYear);

        return array_map(static fn (array $result): array => [
            'year' => $result['year'],
            'total' => $result['total'],
        ], $results);
    }
}
