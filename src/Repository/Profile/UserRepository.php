<?php

declare(strict_types=1);

namespace App\Repository\Profile;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    #[Override]
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Returns a QueryBuilder that excludes the current user and their friends.
     */
    public function getUsersExcludingCurrentUser(User $currentUser): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->andWhere('u.id NOT IN (
                SELECT friend.id FROM App\Entity\Friendship f
                JOIN f.friend friend
                WHERE f.requester = :currentUser AND f.accepted = true
            )')
            ->setParameter('currentUser', $currentUser);
    }
}
