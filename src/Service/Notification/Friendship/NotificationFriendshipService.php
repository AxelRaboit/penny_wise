<?php

declare(strict_types=1);

namespace App\Service\Notification\Friendship;

use App\Entity\User;
use App\Enum\Notification\NotificationTypeEnum;
use App\Manager\Notification\NotificationManager;

final readonly class NotificationFriendshipService
{
    public function __construct(private NotificationManager $notificationManager) {}

    public function createFriendRequestNotification(User $friend, User $requester): void
    {
        $message = sprintf('You have a friend request from %s.', $requester->getUsername());
        $this->notificationManager->createNotification(
            $friend,
            NotificationTypeEnum::FriendshipRequest->getValue(),
            $message
        );
    }
}
