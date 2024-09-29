<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CalendarDto;
use App\Dto\MonthDto;
use App\Dto\YearDto;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Exception\WalletNotFoundWithinLimitException;
use App\Repository\WalletRepository;
use App\Util\WalletHelper;

final readonly class WalletService
{
    public function __construct(
        private WalletRepository $walletRepository,
        private WalletHelper $walletHelper,
    ) {}

    public function getWalletByUser(User $user, int $year, int $month): ?Wallet
    {
        /** @var Wallet|null $wallet */
        $wallet = $this->walletRepository
            ->findWalletByUser($user, $year, $month);

        return $wallet;
    }

    public function findAllWalletByUser(User $user): CalendarDto
    {
        $results = $this->walletRepository->findAllWalletsWithTransactionsByUser($user);

        $yearsAndMonths = [];

        foreach ($results as $result) {
            $year = $result['year'];
            $month = $result['month'];
            $walletId = $result['id'];

            $monthEnum = MonthEnum::from($month);
            if (!isset($yearsAndMonths[$year])) {
                $yearsAndMonths[$year] = [];
            }
            $yearsAndMonths[$year][] = new MonthDto($month, $monthEnum->getName(), $walletId);
        }

        $years = [];
        foreach ($yearsAndMonths as $year => $months) {
            $years[] = new YearDto($year, $months);
        }

        return new CalendarDto($years);
    }

    /**
     * Finds the previous wallet for a given user based on the specified year and month.
     * The method searches backward month-by-month until a wallet is found or the limit of months is reached.
     *
     * @param User $user          the user for whom the previous wallet is being searched
     * @param int  $year          the year from which the search starts
     * @param int  $month         the month from which the search starts
     * @param int  $maxMonthsBack the maximum number of months to search backward
     *
     * @return Wallet|null the found wallet or null if no previous wallet is found within the limit or if the limit is exceeded
     *
     * @throws WalletNotFoundWithinLimitException
     */
    public function findPreviousWallet(User $user, int $year, int $month, int $maxMonthsBack = 12): ?Wallet
    {
        $previousMonthData = $this->walletHelper->findPreviousValidMonthAndYear($year, $month);

        return $this->searchWallet($user, $previousMonthData['year'], $previousMonthData['month'], $maxMonthsBack, $maxMonthsBack);
    }

    /**
     * Recursively searches for a user's wallet starting from a given year and month, going backwards
     * up to a specified limit if a wallet is not found.
     *
     * @param User $user            the user whose wallet is being searched
     * @param int  $year            the starting year for the search
     * @param int  $month           the starting month for the search
     * @param int  $remainingMonths the number of months remaining to search backwards
     * @param int  $maxMonthsBack   the maximum number of months to search backwards
     *
     * @return Wallet|null the found wallet or null if no wallet is found within the allowed limit
     *
     * @throws WalletNotFoundWithinLimitException if the wallet is not found within the specified limit
     */
    private function searchWallet(User $user, int $year, int $month, int $remainingMonths, int $maxMonthsBack): ?Wallet
    {
        if ($remainingMonths <= 0) {
            throw new WalletNotFoundWithinLimitException($maxMonthsBack);
        }

        $wallet = $this->walletRepository->findWalletByUser($user, $year, $month);

        if ($wallet instanceof Wallet) {
            return $wallet;
        }

        $previousMonthData = $this->walletHelper->findPreviousValidMonthAndYear($year, $month);
        $year = $previousMonthData['year'];
        $month = $previousMonthData['month'];
        --$remainingMonths;

        return $this->searchWallet($user, $year, $month, $remainingMonths, $maxMonthsBack);
    }
}
