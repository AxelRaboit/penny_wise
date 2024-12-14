<?php

declare(strict_types=1);

namespace App\Repository\Messenger;

use App\Entity\MessengerParticipant;
use App\Entity\MessengerTalk;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessengerTalk>
 */
final class MessengerTalkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, MessengerTalk::class);
    }

    /**
     * Find an existing talk between two users.
     *
     * @param User $user1 the first user
     * @param User $user2 the second user
     *
     * @return MessengerTalk|null returns a MessengerTalk object if found, or null if not
     */
    public function findExistingTalk(User $user1, User $user2): ?MessengerTalk
    {
        /** @var MessengerTalk|null $result */
        $result = $this->createQueryBuilder('t')
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

        return $result;
    }

    /**
     * Find all talks for a given user.
     *
     * @param int $messengerId the ID of the user's messenger
     *
     * @return MessengerTalk[] returns an array of MessengerTalk objects
     */
    public function findTalksByUser(int $messengerId): array
    {
        /** @var MessengerTalk[] $result */
        $result = $this->createQueryBuilder('t')
            ->innerJoin('t.participants', 'p')
            ->where('p.messenger = :messengerId')
            ->setParameter('messengerId', $messengerId)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Add participants to a new MessengerTalk.
     *
     * @param MessengerTalk $talk  the MessengerTalk entity
     * @param User          $user1 the first user
     * @param User          $user2 the second user
     */
    public function addParticipantsToTalk(MessengerTalk $talk, User $user1, User $user2): void
    {
        $participants = [$user1, $user2];

        foreach ($participants as $user) {
            $messenger = $user->getMessenger();
            $participant = new MessengerParticipant();
            $participant->setMessenger($messenger)->setTalk($talk);

            $this->entityManager->persist($participant);
            $talk->addParticipant($participant);
        }

        $this->entityManager->persist($talk);
        $this->entityManager->flush();
    }

    /**
     * Find all talks for a given user, with optional visibility filtering.
     *
     * @return MessengerTalk[] returns an array of MessengerTalk objects
     */
    public function findTalksByUserWithVisibility(int $messengerId, bool $onlyVisible = true): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.participants', 'p')
            ->where('p.messenger = :messengerId')
            ->setParameter('messengerId', $messengerId);

        if ($onlyVisible) {
            $qb->andWhere('p.isVisibleToParticipant = true');
        }

        /** @var MessengerTalk[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Find an existing talk between two users, whether hidden or visible.
     */
    public function findExistingOrHiddenTalk(User $user1, User $user2): ?MessengerTalk
    {
        /** @var MessengerTalk|null $result */
        $result = $this->createQueryBuilder('t')
            ->join('t.participants', 'p1')
            ->join('p1.messenger', 'm1')
            ->join('m1.user', 'u1')
            ->join('t.participants', 'p2')
            ->join('p2.messenger', 'm2')
            ->join('m2.user', 'u2')
            ->where('(u1 = :user1 AND u2 = :user2) OR (u1 = :user2 AND u2 = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * Find all messages for a given talk, ordered by sentAt.
     *
     * @param MessengerTalk $talk The MessengerTalk entity
     *
     * @return MessengerMessage[] Returns an array of MessengerMessage objects
     */
    public function findMessagesByTalk(MessengerTalk $talk): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('m')
            ->from('App\Entity\MessengerMessage', 'm')
            ->where('m.talk = :talk')
            ->setParameter('talk', $talk)
            ->orderBy('m.sentAt', Order::Ascending->value)
            ->getQuery()
            ->getResult();
    }
}
