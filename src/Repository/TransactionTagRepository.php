<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TransactionTag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionTag>
 */
class TransactionTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionTag::class);
    }

    /**
     * Find transaction tags associated with a specific user.
     *
     * @return array<TransactionTag> An array of TransactionTag objects
     */
    public function findByUser(User $user): array
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->getQuery();

        /** @var array<TransactionTag> $result */
        $result = $query->getResult();

        return $result;
    }

    public function findByUserCount(User $user): int
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }
}
