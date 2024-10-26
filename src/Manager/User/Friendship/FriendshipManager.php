<?php

declare(strict_types=1);

namespace App\Manager\User\Friendship;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\User\Friendship\FriendshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class FriendshipManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FriendshipRepository $friendshipRepository
    ) {}

    public function sendFriendRequest(User $user, User $friend): void
    {
        $friendship = new Friendship();
        $friendship->setRequester($user);
        $friendship->setFriend($friend);
        $friendship->setAccepted(false);

        $this->entityManager->persist($friendship);
        $this->entityManager->flush();
    }

    public function acceptFriendRequest(Friendship $friendship): void
    {
        $friendship->setAccepted(true);

        if (null === $this->friendshipRepository->findOneBy([
            'requester' => $friendship->getFriend(),
            'friend' => $friendship->getRequester(),
        ])) {
            $mutualFriendship = new Friendship();
            $mutualFriendship->setRequester($friendship->getFriend());
            $mutualFriendship->setFriend($friendship->getRequester());
            $mutualFriendship->setAccepted(true);

            $this->entityManager->persist($mutualFriendship);
        }

        $this->entityManager->flush();
    }

    public function declineFriendRequest(Friendship $friendship): void
    {
        $this->entityManager->remove($friendship);
        $this->entityManager->flush();
    }

    public function unfriend(Friendship $friendship): void
    {
        $this->entityManager->remove($friendship);

        $reciprocalFriendship = $this->friendshipRepository->findOneBy([
            'requester' => $friendship->getFriend(),
            'friend' => $friendship->getRequester(),
        ]);

        if (null !== $reciprocalFriendship) {
            $this->entityManager->remove($reciprocalFriendship);
        }

        $this->entityManager->flush();
    }

    public function areFriends(User $user, User $otherUser): bool
    {
        if (null !== $this->friendshipRepository->findOneBy([
            'requester' => $user,
            'friend' => $otherUser,
            'accepted' => true,
        ])) {
            return true;
        }

        return (bool) $this->friendshipRepository->findOneBy([
            'requester' => $otherUser,
            'friend' => $user,
            'accepted' => true,
        ]);
    }

    public function cancelFriendRequest(Friendship $friendship): void
    {
        $this->entityManager->remove($friendship);
        $this->entityManager->flush();
    }
}
