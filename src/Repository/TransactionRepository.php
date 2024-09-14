<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Find transactions by budget and transaction category.
     *
     * @return Transaction[] Returns an array of Transaction objects.
     */
    public function findTransactionsByBudgetAndCategory(Budget $budget, int $transactionCategoryId): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->where('t.budget = :budget')
            ->andWhere('t.transactionCategory = :transactionCategory')
            ->setParameter('budget', $budget)
            ->setParameter('transactionCategory', $transactionCategoryId)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findTransactionsByBudget(Budget $budget): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->where('t.budget = :budget')
            ->setParameter('budget', $budget)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
