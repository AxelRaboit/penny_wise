<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionManager
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function findAndDeleteTransactionsByWallet(Wallet $wallet): void
    {
        $transactions = $this->transactionRepository->findTransactionsByWallet($wallet);
        foreach ($transactions as $transaction) {
            $this->entityManager->remove($transaction);
        }

        $this->entityManager->flush();
    }

    public function copyTransactionsFromPreviousMonth(Wallet $currentWallet, float $totalLeftToSpend): void
    {
        $currentWallet->setStartBalance($totalLeftToSpend);
        $this->entityManager->persist($currentWallet);
        $this->entityManager->flush();
    }

    private function handleTransactionTags(Transaction $transaction): void
    {
        foreach ($transaction->getTag() as $tag) {
            $transaction->addTag($tag);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    public function saveTransaction(Transaction $transaction): void
    {
        $this->handleTransactionTags($transaction);
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
    }
}
