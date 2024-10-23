<?php

declare(strict_types=1);

namespace App\Manager\Account\Wallet\Transaction;

use App\Entity\Wallet;
use App\Repository\Transaction\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class WalletTransactionDeleteManager
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
     * @param Wallet $wallet   The wallet from which transactions will be deleted.
     * @param string $category The category of transactions to delete.
     *
     * @return bool Returns true if transactions were found and deleted, false otherwise.
     */
    public function deleteTransactionsByCategory(Wallet $wallet, string $category): bool
    {
        $transactions = $this->transactionRepository->findTransactionsByCategory($wallet, $category);
        if (empty($transactions)) {
            return false;
        }

        foreach ($transactions as $transaction) {
            $this->entityManager->remove($transaction);
        }

        $this->entityManager->flush();

        return true;
    }
}
