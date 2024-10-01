<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\TransactionCategoryRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class TransactionForWalletManager
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private TransactionCategoryRepository $transactionCategoryRepository,
        private EntityManagerInterface $entityManager
    ) {}

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

    public function prepareTransactionForWallet(Wallet $wallet, User $user): Transaction
    {
        $transaction = new Transaction();
        $transaction->setWallet($wallet);
        $transaction->setIndividual($user);

        return $transaction;
    }

    /**
     * Prepares a transaction for a specific wallet and category.
     *
     * @param Wallet $wallet   the wallet to which the transaction will be associated
     * @param User   $user     the user who is making the transaction
     * @param string $category the category of the transaction
     *
     * @return Transaction the prepared transaction
     *
     * @throws LogicException if the transaction category is not found
     */
    public function prepareTransactionForWalletWithCategory(Wallet $wallet, User $user, string $category): Transaction
    {
        $transactionCategory = $this->transactionCategoryRepository
            ->findOneBy(['name' => $category]);

        if (!$transactionCategory instanceof TransactionCategory) {
            throw new LogicException('Transaction category not found.');
        }

        $transaction = new Transaction();
        $transaction->setWallet($wallet);
        $transaction->setIndividual($user);
        $transaction->setTransactionCategory($transactionCategory);

        return $transaction;
    }
}
