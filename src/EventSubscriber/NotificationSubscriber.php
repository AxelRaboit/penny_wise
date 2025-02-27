<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\Notification\NotificationRepository;
use Override;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final readonly class NotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private Security $security,
        private Environment $twig
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        $notifications = $this->notificationRepository->getUnreadNotificationsByUser($user);
        $unreadNotificationCount = count($notifications);

        $this->twig->addGlobal('notifications', $notifications);
        $this->twig->addGlobal('unreadNotificationCount', $unreadNotificationCount);
    }
}
