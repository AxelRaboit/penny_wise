<?php

namespace App\Service\Messenger;

use App\Entity\MessengerTalk;
use App\Entity\User;
use App\Repository\Messenger\MessengerTalkRepository;

final readonly class MessengerService
{
    public function __construct(
        private MessengerTalkRepository $messengerTalkRepository
    ) {}

    /**
     * Find a talk by its ID.
     */
    public function findTalkById(int $id): ?MessengerTalk
    {
        return $this->messengerTalkRepository->find($id);
    }

    /**
     * Retrieve all talks for a specific user.
     */
    public function getTalksForUser(User $user): array
    {
        $messenger = $user->getMessenger();
        return $this->messengerTalkRepository->findTalksByUser($messenger->getId());
    }

    /**
     * Get the participant in a talk who is not the current user.
     */
    public function getTalkParticipant(MessengerTalk $talk, User $currentUser): ?User
    {
        foreach ($talk->getParticipants() as $participant) {
            if ($participant->getMessenger()->getUser() !== $currentUser) {
                return $participant->getMessenger()->getUser();
            }
        }
        return null;
    }
}
