<?php

declare(strict_types=1);

namespace App\Repository\Messenger;

use App\Entity\MessengerParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessengerParticipant>
 */
class MessengerParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessengerParticipant::class);
    }

    /**
     * Find all active talks for a user (not deleted).
     */
    public function findActiveTalksByUser(int $messengerId): array
    {
        /** @var array<MessengerParticipant> $result */
        $result = $this->createQueryBuilder('p')
            ->andWhere('p.messenger = :messenger')
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('messenger', $messengerId)
            ->setParameter('isDeleted', false)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
