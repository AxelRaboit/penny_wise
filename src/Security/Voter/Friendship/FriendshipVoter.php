<?php

declare(strict_types=1);

namespace App\Security\Voter\Friendship;

use App\Entity\Friendship;
use App\Entity\User;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Friendship>
 */
final class FriendshipVoter extends Voter
{
    public const string UNFRIEND = 'UNFRIEND';

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::UNFRIEND === $attribute && $subject instanceof Friendship;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Friendship $friendship */
        $friendship = $subject;

        return $this->isUserInvolvedInFriendship($user, $friendship);
    }

    private function isUserInvolvedInFriendship(User $user, Friendship $friendship): bool
    {
        if ($this->isRequester($user, $friendship)) {
            return true;
        }

        return $this->isFriend($user, $friendship);
    }

    private function isRequester(User $user, Friendship $friendship): bool
    {
        return $friendship->getRequester() === $user;
    }

    private function isFriend(User $user, Friendship $friendship): bool
    {
        return $friendship->getFriend() === $user;
    }
}
