<?php

declare(strict_types=1);

namespace App\Repository\Test;

use App\Entity\Transaction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final readonly class InMemoryTransactionRepository
{
    /**
     * @var Collection<int, Transaction>
     */
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function save(Transaction $transaction): void
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
        }
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?Transaction
    {
        foreach ($this->transactions as $transaction) {
            if (isset($criteria['id']) && $transaction->getId() === $criteria['id']) {
                return $transaction;
            }

            if (isset($criteria['description']) && $transaction->getDescription() === $criteria['description']) {
                return $transaction;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function findAll(): Collection
    {
        return $this->transactions;
    }

    public function delete(Transaction $transaction): void
    {
        $this->transactions->removeElement($transaction);
    }
}
