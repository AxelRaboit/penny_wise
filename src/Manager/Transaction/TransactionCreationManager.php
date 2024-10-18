<?php

declare(strict_types=1);

namespace App\Manager\Transaction;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionCreationManager
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function beginTransactionCreation(): Transaction
    {
        return new Transaction();
    }

    public function saveTransaction(Transaction $transaction, User $user): void
    {
        $transaction->setUser($user);
        $this->handleTransactionTags($transaction);
        $this->entityManager->persist($transaction);
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
}
