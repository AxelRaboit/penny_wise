<?php

declare(strict_types=1);

namespace App\Manager\Messenger;

use App\Entity\Friendship;
use App\Entity\Messenger;
use App\Entity\MessengerMessage;
use App\Entity\MessengerParticipant;
use App\Entity\MessengerTalk;
use App\Entity\User;
use App\Repository\Messenger\MessengerTalkRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MessengerManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessengerTalkRepository $messengerTalkRepository
    ) {}

    /**
     * Retrieve all talks for a specific user.
     *
     * @return MessengerTalk[]
     */
    public function getTalksForUser(User $user): array
    {
        $messenger = $user->getMessenger();
        $messengerId = $messenger->getId();

        if (null === $messengerId) {
            return [];
        }

        return $this->messengerTalkRepository->findTalksByUserWithVisibility($messengerId);
    }


    /**
     * Get the participant in a talk who is not the current user.
     */
    public function getTalkParticipant(MessengerTalk $talk, User $currentUser): ?User
    {
        foreach ($talk->getParticipants() as $participant) {
            $messenger = $participant->getMessenger();
            if (null !== $messenger && $messenger->getUser() !== $currentUser) {
                return $messenger->getUser();
            }
        }

        return null;
    }

    public function addMessage(MessengerTalk $talk, User $sender, string $content): void
    {
        $message = new MessengerMessage();
        $message->setContent($content)
            ->setSender($sender)
            ->setTalk($talk);

        $this->entityManager->persist($message);

        foreach ($talk->getParticipants() as $participant) {
            if ($participant->getMessenger()->getUser() !== $sender && !$participant->isVisibleToParticipant()) {
                $participant->setVisibleToParticipant(true);
                $this->entityManager->persist($participant);
            }
        }

        $this->entityManager->flush();
    }

    public function createTalk(User $user, User $friend): MessengerTalk
    {
        $talk = new MessengerTalk();

        $userParticipant = new MessengerParticipant();
        $userParticipant->setTalk($talk)->setMessenger($user->getMessenger())->setVisibleToParticipant(true);
        $this->entityManager->persist($userParticipant);

        $friendParticipant = new MessengerParticipant();
        $friendParticipant->setTalk($talk)->setMessenger($friend->getMessenger())->setVisibleToParticipant(false);
        $this->entityManager->persist($friendParticipant);

        $this->entityManager->persist($talk);
        $this->entityManager->flush();

        return $talk;
    }

    /**
     * @param User $user
     * @return array<Messenger>
     */
    public function getFriendsForNewConversation(User $user): array
    {
        $friends = $user->getAcceptedFriends()->map(fn(Friendship $friendship) => $friendship->getFriend())->toArray();

        $existingTalkFriendIds = [];
        foreach ($this->getTalksForUser($user) as $talk) {
            foreach ($talk->getParticipants() as $participant) {
                if ($participant->getMessenger()->getUser() !== $user) {
                    $existingTalkFriendIds[] = $participant->getMessenger()->getUser()->getId();
                }
            }
        }

        return array_filter($friends, fn(User $friend) => !in_array($friend->getId(), $existingTalkFriendIds, true));
    }
}
