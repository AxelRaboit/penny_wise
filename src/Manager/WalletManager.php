<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Exception\NoPreviousWalletException;
use App\Service\WalletService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

final readonly class WalletManager
{
    public function __construct(private EntityManagerInterface $entityManager, private WalletService $walletService, private TransactionManager $transactionManager) {}

    /**
     * Create a wallet for a given user, year, and month.
     *
     * @throws Exception
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
        $newWallet->setStartBalance(0.0);

        $this->entityManager->persist($newWallet);
        $this->entityManager->flush();
    }

    public function deleteWalletForMonth(User $user, int $year, int $month): void
    {
        $wallet = $this->walletService->getWalletByUser($user, $year, $month);
        if (!$wallet instanceof Wallet) {
            throw new NoPreviousWalletException();
        }

        $this->transactionManager->findAndDeleteTransactionsByWallet($wallet);

        $this->entityManager->remove($wallet);
        $this->entityManager->flush();
    }
}
