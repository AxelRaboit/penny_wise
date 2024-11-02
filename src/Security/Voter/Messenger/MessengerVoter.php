<?php

namespace App\Security\Voter\Messenger;

use App\Entity\MessengerTalk;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, MessengerTalk>
 */
final class MessengerVoter extends Voter
{
    public const string VIEW = 'VIEW';
    public const string MESSAGE = 'MESSAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::MESSAGE]) && $subject instanceof MessengerTalk;
    }

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
            if ($participant->getMessenger()->getUser() === $user) {
                return true;
            }
        }

        return false;
    }
}
