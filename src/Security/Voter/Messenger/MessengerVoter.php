<?php

declare(strict_types=1);

namespace App\Security\Voter\Messenger;

use App\Entity\MessengerTalk;
use App\Entity\User;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, MessengerTalk>
 */
final class MessengerVoter extends Voter
{
    public const string VIEW = 'VIEW';

    public const string SEND_MESSAGE = 'SEND_MESSAGE';

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::SEND_MESSAGE], true) && $subject instanceof MessengerTalk;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var MessengerTalk $talk */
        $talk = $subject;

        return $this->isParticipant($user, $talk);
    }

    private function isParticipant(User $user, MessengerTalk $talk): bool
    {
        foreach ($talk->getParticipants() as $participant) {
            $messenger = $participant->getMessenger();
            if (null !== $messenger && $messenger->getUser() === $user) {
                return true;
            }
        }

        return false;
    }
}
