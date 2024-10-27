<?php

declare(strict_types=1);

namespace App\Manager\Notification;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\Notification\NotificationRepository;
use App\Service\User\UserCheckerService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NotificationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationRepository $notificationRepository,
        private UserCheckerService $userCheckerService
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

    public function markAsReadAndDelete(Notification $notification): void
    {
        $notification->markAsRead();
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    public function markAllAsReadAndDelete(): void
    {
        $user = $this->userCheckerService->getUserOrThrow();

        $notifications = $this->notificationRepository->findBy(['user' => $user, 'isRead' => false]);

        foreach ($notifications as $notification) {
            $notification->markAsRead();
            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();
    }

    /**
     * @return array<Notification>
     */
    public function getUserNotifications(): array
    {
        $user = $this->userCheckerService->getUserOrThrow();

        return $this->notificationRepository->findBy(['user' => $user, 'isRead' => false]);
    }
}
