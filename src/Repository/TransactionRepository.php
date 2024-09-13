<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Enum\TransactionTypeEnum;
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
     * @param Budget $budget
     * @param int $transactionCategoryId
     * @return array
     */
    public function findTransactionsByBudgetAndCategory(Budget $budget, int $transactionCategoryId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.budget = :budget')
            ->andWhere('t.transactionCategory = :transactionCategory')
            ->setParameter('budget', $budget)
            ->setParameter('transactionCategory', $transactionCategoryId)
            ->getQuery()
            ->getResult();
    }
}
