<?php

declare(strict_types=1);

namespace App\Security\Voter\Notification;

use App\Entity\Notification;
use App\Entity\User;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Notification>
 */
final class NotificationVoter extends Voter
{
    public const string MARK_NOTIFICATION_AS_READ = 'MARK_NOTIFICATION_AS_READ';

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MARK_NOTIFICATION_AS_READ === $attribute && $subject instanceof Notification;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Notification $notification */
        $notification = $subject;

        return $this->isOwner($user, $notification);
    }

    private function isOwner(User $user, Notification $notification): bool
    {
        return $notification->getUser() === $user;
    }
}
