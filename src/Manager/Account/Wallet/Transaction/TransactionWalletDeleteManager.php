<?php

namespace App\Manager\Account\Wallet\Transaction;

use App\Entity\Wallet;
use App\Repository\Transaction\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionWalletDeleteManager
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Deletes all transactions from the given wallet.
     *
     * @param Wallet $wallet the wallet from which all transactions will be deleted
     */
    public function deleteTransactionsByWallet(Wallet $wallet): void
    {
        $transactions = $this->transactionRepository->findTransactionsByWallet($wallet);
        foreach ($transactions as $transaction) {
            $this->entityManager->remove($transaction);
        }

        $this->entityManager->flush();
    }

    /**
     * Deletes transactions from a given wallet that match the specified category.
     *
     * @param Wallet $wallet   the wallet from which transactions will be deleted
     * @param string $category the category of transactions to delete
     *
     * @return bool returns true if transactions were found and deleted, false otherwise
     */
    public function deleteTransactionsByCategory(Wallet $wallet, string $category): bool
    {
        $transactions = $this->transactionRepository->findTransactionsByCategory($wallet, $category);
        if ([] === $transactions) {
            return false;
        }

        foreach ($transactions as $transaction) {
            $this->entityManager->remove($transaction);
        }

        $this->entityManager->flush();

        return true;
    }
}