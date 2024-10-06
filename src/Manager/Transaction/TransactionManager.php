<?php

declare(strict_types=1);

namespace App\Manager\Transaction;

use App\Entity\Transaction;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function copyTransactionsFromPreviousMonth(Wallet $currentWallet, float $totalLeftToSpend): void
    {
        $currentWallet->setStartBalance($totalLeftToSpend);
        $this->entityManager->persist($currentWallet);
        $this->entityManager->flush();
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
    }
}
