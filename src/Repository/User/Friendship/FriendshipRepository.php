<?php

declare(strict_types=1);

namespace App\Repository\User\Friendship;

use App\Entity\Friendship;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friendship>
 */
class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    /**
     * @return array<int, Friendship>
     */
    public function findAcceptedFriendships(User $user): array
    {
        /** @var array<int, Friendship> $result */
        $result = $this->createQueryBuilder('f')
            ->where('(f.requester = :user OR f.friend = :user) AND f.accepted = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return array<int, Friendship>
     */
    public function findPendingFriendRequests(User $user): array
    {
        /** @var array<int, Friendship> $result */
        $result = $this->createQueryBuilder('f')
            ->where('f.friend = :user AND f.accepted = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
