<?php

declare(strict_types=1);

namespace App\Repository\Security;

use App\Entity\LoginAttempt;
use App\Entity\User;
use DateMalformedStringException;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginAttempt>
 */
class LoginAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    /**
     * Delete old login attempts older than the specified number of days.
     *
     * @param int $days the number of days to retain login attempts
     *
     * @throws DateMalformedStringException
     */
    public function deleteOldAttempts(int $days = 30): void
    {
        $this->createQueryBuilder('la')
            ->delete()
            ->where('la.lastAttemptAt < :date')
            ->setParameter('date', (new DateTime())->modify(sprintf('-%d days', $days)))
            ->getQuery()
            ->execute();
    }

    public function deleteByUser(User $user): void
    {
        $this->createQueryBuilder('la')
            ->delete()
            ->where('la.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
