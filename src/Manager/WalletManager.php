<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Service\WalletService;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

final readonly class WalletManager
{
    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(private EntityManagerInterface $entityManager, private WalletService $walletService, private TransactionManager $transactionManager) {}

    /**
     * Creates a new wallet for a specified user for a given month and year.
     *
     * @param User      $user          the user for whom the wallet is being created
     * @param int       $year          the year for which the wallet is being created
     * @param MonthEnum $monthEnum     an enumeration value representing the month
     * @param Wallet    $currentWallet the current wallet to use as a reference for currency
     *
     * @throws DateMalformedStringException
     */
    public function createWalletForMonth(User $user, int $year, MonthEnum $monthEnum, Wallet $currentWallet): void
    {
        $newWallet = new Wallet();

        $firstDayOfMonth = sprintf('%d-%02d-01', $year, $monthEnum->value);
        $startDate = new DateTimeImmutable($firstDayOfMonth);
        $endDate = $startDate->modify('last day of this month');

        $newWallet->setCurrency($currentWallet->getCurrency());
        $newWallet->setStartDate($startDate);
        $newWallet->setEndDate($endDate);
        $newWallet->setIndividual($user);
        $newWallet->setYear($year);
        $newWallet->setMonth($monthEnum->value);
        $newWallet->setStartBalance(self::DEFAULT_BALANCE);

        $this->entityManager->persist($newWallet);
        $this->entityManager->flush();
    }

    /**
     * Deletes the wallet for a given user for a specific month and year, along with its associated transactions.
     *
     * @param User $user  the user whose wallet will be deleted
     * @param int  $year  the year for which the wallet is being deleted
     * @param int  $month the month for which the wallet is being deleted
     *
     * @throws NotFoundResourceException if the wallet for the given year and month is not found
     */
    public function deleteWalletForMonth(User $user, int $year, int $month): void
    {
        $wallet = $this->walletService->getWalletByUser($user, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundResourceException('Wallet not found for the given year and month');
        }

        $this->transactionManager->findAndDeleteTransactionsByWallet($wallet);

        $this->entityManager->remove($wallet);
        $this->entityManager->flush();
    }

    /**
     * Resets the start balance for a given user's wallet for a specific month and year to a default value.
     *
     * @param User $user  the user whose wallet start balance will be reset
     * @param int  $year  the year for which the wallet start balance is being reset
     * @param int  $month the month for which the wallet start balance is being reset
     *
     * @throws NotFoundResourceException if the wallet for the given year and month is not found
     */
    public function resetStartBalanceForMonth(User $user, int $year, int $month): void
    {
        $wallet = $this->walletService->getWalletByUser($user, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw new NotFoundResourceException('Wallet not found for the given year and month');
        }

        $wallet->setStartBalance(self::DEFAULT_BALANCE);
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }
}
