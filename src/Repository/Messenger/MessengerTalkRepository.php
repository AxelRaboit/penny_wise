<?php

declare(strict_types=1);

namespace App\Repository\Messenger;

use App\Entity\MessengerTalk;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessengerTalk>
 */
class MessengerTalkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessengerTalk::class);
    }

    public function findExistingTalk(User $user1, User $user2): ?MessengerTalk
    {
        return $this->createQueryBuilder('t')
            ->join('t.participants', 'p1')
            ->join('p1.messenger', 'm1')
            ->join('m1.user', 'u1')
            ->join('t.participants', 'p2')
            ->join('p2.messenger', 'm2')
            ->join('m2.user', 'u2')
            ->where('u1 = :user1')
            ->andWhere('u2 = :user2')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all talks for a given user.
     *
     * @param int $messengerId The ID of the user's messenger.
     * @return MessengerTalk[] Returns an array of MessengerTalk objects.
     */
    public function findTalksByUser(int $messengerId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.participants', 'p')
            ->where('p.messenger = :messengerId')
            ->setParameter('messengerId', $messengerId)
            ->getQuery()
            ->getResult();
    }


}
