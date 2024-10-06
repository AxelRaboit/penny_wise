<?php

declare(strict_types=1);

namespace App\Manager\Refacto\Account\Wallet\Transaction;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\User;
use App\Entity\Wallet;
use App\Manager\Refacto\Transaction\TransactionCreationManager;
use App\Repository\Transaction\TransactionCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class WalletTransactionCreationManager
{
    public function __construct(
        private TransactionCreationManager $transactionCreationManager,
        private TransactionCategoryRepository $transactionCategoryRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function beginTransactionCreationWithWallet(Wallet $wallet, User $user): Transaction
    {
        $transaction = $this->transactionCreationManager->beginTransactionCreation();
        $transaction->setWallet($wallet);
        $transaction->setIndividual($user);

        return $transaction;
    }

    public function saveTransactionWallet(Transaction $transaction): void
    {
        $this->handleTransactionWalletTags($transaction);
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
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
    public function beginTransactionWithWalletAndCategoryCreation(Wallet $wallet, User $user, string $category): Transaction
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

    private function handleTransactionWalletTags(Transaction $transaction): void
    {
        foreach ($transaction->getTag() as $tag) {
            $transaction->addTag($tag);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }
}
