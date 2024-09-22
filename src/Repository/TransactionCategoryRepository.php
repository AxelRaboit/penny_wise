<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TransactionCategory;
use App\Enum\TransactionTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionCategory>
 */
class TransactionCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionCategory::class);
    }

    public function getAllExceptSavings(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.name NOT IN (:values)')
            ->setParameter('values', [TransactionTypeEnum::SAVINGS->getString()]);
    }

    public function findIdByCategoryName(string $categoryName): ?int
    {
        try {
            return (int) $this->createQueryBuilder('tc')
                ->select('tc.id')
                ->where('tc.name = :name')
                ->setParameter('name', $categoryName)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException) {
            return null;
        }
    }
}
