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
     * @throws DateMalformedStringException
     */
    public function findOrCreateByUser(User $user): LoginAttempt
    {
        /** @var LoginAttempt|null $loginAttempt */
        $loginAttempt = $this->findOneBy(['user' => $user]);
        if (null === $loginAttempt) {
            $loginAttempt = new LoginAttempt($user);
            $this->save($loginAttempt);
        }

        return $loginAttempt;
    }

    public function save(LoginAttempt $loginAttempt): void
    {
        $this->getEntityManager()->persist($loginAttempt);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function deleteOldAttempts(int $days = 30): void
    {
        $qb = $this->createQueryBuilder('la')
            ->delete()
            ->where('la.lastAttemptAt < :date')
            ->setParameter('date', (new DateTime())->modify(sprintf('-%d days', $days)));

        $qb->getQuery()->execute();
    }
}
