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
    private const int BILL_CATEGORY_ID = 1;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findBillsByBudget(Budget $budget): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.budget = :budget')
            ->andWhere('t.transactionCategory = :billCategory')
            ->setParameter('budget', $budget)
            ->setParameter('billCategory', self::BILL_CATEGORY_ID)
            ->getQuery()
            ->getResult();
    }
}
