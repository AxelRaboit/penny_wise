<?php

declare(strict_types=1);

namespace App\Manager\Refacto\TransactionTag;

use App\Entity\TransactionTag;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionTagManager
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function deleteTransactionTag(TransactionTag $transactionTag): void
    {
        $this->entityManager->remove($transactionTag);
        $this->entityManager->flush();
    }
}
