<?php

namespace App\Repository\Notification;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @param int $userId
     * @return array<Notification>
     */
    public function findUnreadNotificationsByUser(int $userId): array
    {
        /** @var array<Notification> $result */
        $result = $this->createQueryBuilder('n')
            ->where('n.user = :userId')
            ->andWhere('n.isRead = false')
            ->setParameter('userId', $userId)
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param User $user
     * @return array<Notification>
     */
    public function getUnreadNotificationsByUser(User $user): array
    {
        /** @var array<Notification> $result */
        $result = $this->createQueryBuilder('n')
            ->where('n.user = :userId')
            ->andWhere('n.isRead = false')
            ->setParameter('userId', $user->getId())
            ->orderBy('n.createdAt', Order::Descending->value)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
