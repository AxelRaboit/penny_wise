<?php

declare(strict_types=1);

namespace App\Manager\Refacto\Transaction;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionCreationManager
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function beginTransactionCreation(): Transaction
    {
        return new Transaction();
    }

    public function saveTransaction(Transaction $transaction): void
    {
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
