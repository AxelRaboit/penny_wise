<?php

declare(strict_types=1);

namespace App\Manager\Account\Wallet;

use App\Entity\Account;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\Wallet\MonthEnum;
use App\Manager\Account\Wallet\Transaction\WalletTransactionDeleteManager;
use App\Repository\Wallet\WalletRepository;
use App\Service\Checker\Wallet\WalletCheckerService;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AccountWalletManager
{
    public const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private readonly WalletCheckerService $walletCheckerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly WalletTransactionDeleteManager $transactionWalletDeleteManager,
        private readonly WalletRepository $walletRepository,
    ) {}

    /**
     * Creates a new wallet for a specified user for a given year and month.
     *
     * This method ensures that no duplicate wallet is created for the same year, month, and account.
     * If a wallet already exists for the given parameters, a LogicException will be thrown.
     *
     * @param User      $user          the user for whom the wallet is being created
     * @param int       $year          the year for which the wallet is being created
     * @param MonthEnum $monthEnum     the month for which the wallet is being created, represented by a MonthEnum value
     * @param Wallet    $currentWallet the existing wallet to use as a reference for setting attributes like currency
     * @param Account   $account       the account to link the new wallet to
     *
     * @throws DateMalformedStringException if the provided date string is not correctly formatted
     * @throws LogicException               if a wallet for the given account, year, and month already exists
     */
    public function createWalletForMonth(User $user, int $year, MonthEnum $monthEnum, Wallet $currentWallet, Account $account): void
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new LogicException('Account ID cannot be null');
        }

        $this->walletCheckerService->ensureWalletDoesNotExist($accountId, $year, $monthEnum->value);

        $newWallet = new Wallet();

        $firstDayOfMonth = sprintf('%d-%02d-01', $year, $monthEnum->value);
        $startDate = new DateTimeImmutable($firstDayOfMonth);
        $endDate = $startDate->modify('last day of this month');

        $newWallet->setCurrency($currentWallet->getCurrency());
        $newWallet->setStartDate($startDate);
        $newWallet->setEndDate($endDate);
        $newWallet->setIndividual($user);
        $newWallet->setAccount($account);
        $newWallet->setYear($year);
        $newWallet->setMonth($monthEnum->value);
        $newWallet->setStartBalance(self::DEFAULT_BALANCE);

        $this->entityManager->persist($newWallet);
        $this->entityManager->flush();
    }

    /**
     * Deletes the wallet for a given user for a specific month and year, along with its associated transactions.
     *
     * @param int $accountId the ID of the account
     * @param int $year      the year for which the wallet is being deleted
     * @param int $month     the month for which the wallet is being deleted
     *
     * @throws NotFoundResourceException if the wallet for the given year and month is not found
     */
    public function deleteWalletForMonth(int $accountId, int $year, int $month): void
    {
        $wallet = $this->walletRepository
            ->findWalletByAccountYearAndMonth($accountId, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundResourceException('Wallet not found for the given year and month');
        }

        $this->transactionWalletDeleteManager->deleteTransactionsByWallet($wallet);

        $this->entityManager->remove($wallet);
        $this->entityManager->flush();
    }

    /**
     * Resets the balance for a given user's wallet.
     *
     * @param User $user  the user whose wallet start balance will be reset
     * @param int  $year  the year for which the wallet start balance is being reset
     * @param int  $month the month for which the wallet start balance is being reset
     *
     * @throws NotFoundResourceException if the wallet for the given year and month is not found
     */
    public function resetBalance(User $user, int $year, int $month): void
    {
        $wallet = $this->walletRepository
            ->findWalletByUser($user, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundResourceException('Wallet not found for the given year and month');
        }

        $wallet->setStartBalance(self::DEFAULT_BALANCE);
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }
}
