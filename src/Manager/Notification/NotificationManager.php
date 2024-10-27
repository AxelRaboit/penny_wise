<?php

namespace App\Manager\Notification;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\Notification\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NotificationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationRepository $notificationRepository
    ) {}

    public function createNotification(User $recipient, string $type, string $message): void
    {
        $notification = new Notification();
        $notification->setUser($recipient);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setIsRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
        $this->entityManager->flush();
    }

    /**
     * @return array<Notification>
     */
    public function getUserNotifications(User $user): array
    {
        return $this->notificationRepository->findBy(['user' => $user, 'isRead' => false]);
    }
}
