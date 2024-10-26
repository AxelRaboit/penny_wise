<?php

declare(strict_types=1);

namespace App\Repository\User\Friendship;

use App\Dto\Friendship\FriendshipDto;
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

    /**
     * @return array<int, Friendship>
     */
    public function findSentPendingRequests(User $user): array
    {
        /** @var array<int, Friendship> $result */
        $result = $this->createQueryBuilder('f')
            ->where('f.requester = :user')
            ->andWhere('f.accepted = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return array<int, Friendship>
     */
    public function findSentFriendRequests(User $user): array
    {
        /** @var array<int, Friendship> $result */
        $result = $this->createQueryBuilder('f')
            ->where('f.requester = :user')
            ->andWhere('f.accepted = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Find a FriendshipDto between two users.
     *
     * @return FriendshipDto|null Returns a data transfer object with friendship information by user for a given user
     */
    public function findFriendshipDtoBetweenUsers(User $currentUser, User $profileUser): ?FriendshipDto
    {
        $friendship = $this->createQueryBuilder('f')
            ->where('(f.requester = :currentUser AND f.friend = :profileUser) OR (f.requester = :profileUser AND f.friend = :currentUser)')
            ->andWhere('f.requester < f.friend')
            ->setParameter('currentUser', $currentUser)
            ->setParameter('profileUser', $profileUser)
            ->getQuery()
            ->getOneOrNullResult();

        /** @var Friendship|null $friendship */
        if ($friendship && null !== $friendship->getId()) {
            $friend = $friendship->getFriend() === $currentUser ? $friendship->getRequester() : $friendship->getFriend();

            if ($friend instanceof User) {
                return FriendshipDto::createFrom([
                    'id' => $friendship->getId(),
                    'friend' => $friend,
                    'isAccepted' => $friendship->isAccepted(),
                ]);
            }
        }

        return null;
    }

    public function findReciprocalFriendship(Friendship $friendship): ?Friendship
    {
        return $this->findOneBy([
            'requester' => $friendship->getFriend(),
            'friend' => $friendship->getRequester(),
        ]);
    }

    public function isFriend(User $userA, User $userB): bool
    {
        return null !== $this->createQueryBuilder('f')
                ->where('(f.requester = :userA AND f.friend = :userB) OR (f.requester = :userB AND f.friend = :userA)')
                ->andWhere('f.accepted = true')
                ->andWhere('f.requester < f.friend')
                ->setParameter('userA', $userA)
                ->setParameter('userB', $userB)
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function isFriendRequestPending(User $userA, User $userB): bool
    {
        return null !== $this->createQueryBuilder('f')
                ->where('(f.requester = :userA AND f.friend = :userB) OR (f.requester = :userB AND f.friend = :userA)')
                ->andWhere('f.accepted = false')
                ->andWhere('f.requester < f.friend')
                ->setParameter('userA', $userA)
                ->setParameter('userB', $userB)
                ->getQuery()
                ->getOneOrNullResult();
    }
}
