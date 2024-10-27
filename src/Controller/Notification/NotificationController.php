<?php

declare(strict_types=1);

namespace App\Controller\Notification;

use App\Entity\Notification;
use App\Manager\Notification\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class NotificationController extends AbstractController
{
    public function __construct(
        private readonly NotificationManager $notificationManager
    ) {}

    #[Route('/notifications/mark-as-read/{id}', name: 'notification_mark_as_read', methods: ['POST'])]
    #[IsGranted('MARK_NOTIFICATION_AS_READ', subject: 'notification')]
    public function markAsRead(
        Notification $notification,
    ): JsonResponse {
        $this->notificationManager->markAsRead($notification);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/notifications/mark-all-as-read', name: 'notification_mark_all_as_read', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAllAsRead(): JsonResponse
    {
        $this->notificationManager->markAllAsRead();

        return new JsonResponse(['success' => true]);
    }

}
