<?php

declare(strict_types=1);

namespace App\Service\Account\Wallet;

use App\Dto\Account\AccountDto;
use App\Dto\Wallet\MonthDto;
use App\Dto\Wallet\YearDto;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\Wallet\MonthEnum;
use App\Exception\WalletNotFoundWithinLimitException;
use App\Repository\Account\AccountRepository;
use App\Repository\Wallet\WalletRepository;
use App\Util\WalletHelper;
use DateMalformedStringException;
use DateTime;

final readonly class WalletService
{
    public function __construct(
        private WalletRepository $walletRepository,
        private AccountRepository $accountRepository,
        private WalletHelper $walletHelper,
    ) {}

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

    /**
     * Retrieves all accounts with associated wallets for a given user.
     *
     * @param User $user the user for whom the accounts and wallets are retrieved
     *
     * @return array<AccountDto> an array of AccountDto objects
     */
    public function findAllAccountsWithWalletsByUser(User $user): array
    {
        $accounts = $this->accountRepository->findAllAccountsWithWalletsByUser($user);
        $accountsAndWallets = [];

        foreach ($accounts as $account) {
            if (null === $account->getId()) {
                continue;
            }

            $yearsAndMonths = [];
            $walletsAccount = $account->getWallets();

            foreach ($walletsAccount as $wallet) {
                $walletId = $wallet->getId();
                if (null === $walletId) {
                    continue;
                }

                $year = $wallet->getYear();
                $month = $wallet->getMonth();

                $monthEnum = MonthEnum::from($month);
                if (!isset($yearsAndMonths[$year])) {
                    $yearsAndMonths[$year] = [];
                }

                $monthData = [
                    'month' => $month,
                    'month_name' => $monthEnum->getName(),
                    'wallet_id' => $walletId,
                ];

                $yearsAndMonths[$year][] = MonthDto::createFrom($monthData);
            }

            foreach ($yearsAndMonths as $year => $months) {
                usort($months, fn (MonthDto $a, MonthDto $b): int => $a->getMonthNumber() <=> $b->getMonthNumber());
                $yearsAndMonths[$year] = $months;
            }

            krsort($yearsAndMonths);

            $years = [];
            foreach ($yearsAndMonths as $year => $months) {
                $years[] = new YearDto($year, array_values($months));
            }

            $accountData = [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'years' => $years,
                'identifier' => $account->getIdentifier(),
            ];

            $accountsAndWallets[] = AccountDto::createFrom($accountData);
        }

        return $accountsAndWallets;
    }

    public function getWalletByAccountYearAndMonth(int $accountId, int $year, int $month): ?Wallet
    {
        return $this->walletRepository->findWalletByAccountYearAndMonth($accountId, $year, $month);
    }

    /**
     * Retrieves wallets by account and year and organizes them into YearDto objects.
     *
     * @param int $accountId The account ID
     * @param int $year      The year
     *
     * @return array<YearDto> An array of YearDto objects containing the wallets for the given account and year
     */
    public function getWalletsByAccountAndYear(int $accountId, int $year): array
    {
        $results = $this->walletRepository->getAllWalletsAndTransactionsByAccountAndYear($accountId, $year);

        $yearsAndMonths = [];

        foreach ($results as $wallet) {
            $walletId = $wallet->getId();

            if (null === $walletId) {
                continue;
            }

            $year = $wallet->getYear();
            $month = $wallet->getMonth();

            $monthEnum = MonthEnum::from($month);
            if (!isset($yearsAndMonths[$year])) {
                $yearsAndMonths[$year] = [];
            }

            $yearsAndMonths[$year][] = new MonthDto($month, $monthEnum->getName(), $walletId);
        }

        $years = [];
        foreach ($yearsAndMonths as $year => $months) {
            $years[] = new YearDto($year, array_values($months));
        }

        return $years;
    }

    /**
     * Finds the previous and next wallets for a given account and current year/month.
     *
     * @param int $accountId The account ID
     * @param int $year      The current year
     * @param int $month     The current month
     *
     * @return array{
     *     navigationPreviousWallet: MonthDto|null,
     *     navigationNextWallet: MonthDto|null
     * }
     *
     * @throws DateMalformedStringException
     */
    public function getWalletNavigationForCurrentMonth(int $accountId, int $year, int $month): array
    {
        $previousDate = (new DateTime())->setDate($year, $month, 1)->modify('-1 month');
        $previousYear = (int) $previousDate->format('Y');
        $previousMonth = (int) $previousDate->format('m');
        $previousWallet = $this->getWalletByAccountYearAndMonth($accountId, $previousYear, $previousMonth);

        $nextDate = (new DateTime())->setDate($year, $month, 1)->modify('+1 month');
        $nextYear = (int) $nextDate->format('Y');
        $nextMonth = (int) $nextDate->format('m');
        $nextWallet = $this->getWalletByAccountYearAndMonth($accountId, $nextYear, $nextMonth);

        return [
            'navigationPreviousWallet' => $previousWallet instanceof Wallet && null !== $previousWallet->getId()
                ? new MonthDto($previousMonth, MonthEnum::from($previousMonth)->getName(), $previousWallet->getId())
                : null,
            'navigationNextWallet' => $nextWallet instanceof Wallet && null !== $nextWallet->getId()
                ? new MonthDto($nextMonth, MonthEnum::from($nextMonth)->getName(), $nextWallet->getId())
                : null,
        ];
    }
}
