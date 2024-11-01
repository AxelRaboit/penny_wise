<?php

declare(strict_types=1);

namespace App\Security\Voter\Friendship;

use App\Entity\User;
use App\Repository\Profile\UserRepository;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, User>
 */
final class FriendshipProfileVoter extends Voter
{
    public const string VIEW_PROFILE = 'VIEW_PROFILE';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger
    ) {}

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW_PROFILE === $attribute && $subject instanceof User;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $profileUser */
        $profileUser = $subject;

        return match ($attribute) {
            self::VIEW_PROFILE => $this->canViewProfile($currentUser, $profileUser),
            default => false,
        };
    }

    private function canViewProfile(User $currentUser, User $profileUser): bool
    {
        if ($currentUser === $profileUser) {
            return true;
        }

        $username = $profileUser->getUsername();

        if (null === $username) {
            $this->logger->error('Attempted to view a profile with a null username.', [
                'currentUser' => $currentUser->getId(),
                'profileUser' => $profileUser->getId(),
            ]);

            return false;
        }

        return $this->userRepository->findFriendByUsername($currentUser, $username) instanceof User;
    }
}
