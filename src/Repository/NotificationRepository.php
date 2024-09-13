<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\Notification;
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
     * Retrieves the last N notifications.
     *
     * @param int $nth Number of notifications to retrieve
     * @return Notification[] Returns an array of Notification objects
     */
    public function getLastNthNotificationsFromBudget(Budget $budget, int $nth): array
    {
        /** @var Notification[] $notifications */
        $notifications = $this->createQueryBuilder('n')
            ->where('n.budget = :budget')
            ->setParameter('budget', $budget)
            ->orderBy('n.id', Order::Descending->value)
            ->setMaxResults($nth)
            ->getQuery()
            ->getResult();

        return $notifications;
    }

    /**
     * Retrieves notifications associated with a specific budget.
     *
     * @return Notification[] Returns an array of Notification objects
     */
    public function getNotificationsFromBudget(Budget $budget): array
    {
        /** @var Notification[] $notifications */
        $notifications = $this->createQueryBuilder('n')
            ->where('n.budget = :budget')
            ->setParameter('budget', $budget)
            ->orderBy('n.id', Order::Descending->value)
            ->getQuery()
            ->getResult();

        return $notifications;
    }
}
