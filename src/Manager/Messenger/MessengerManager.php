<?php

declare(strict_types=1);

namespace App\Manager\Messenger;

use App\Entity\MessengerMessage;
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

        return $this->messengerTalkRepository->findTalksByUser($messengerId);
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

    /**
     * Add a message to a conversation.
     */
    public function addMessage(MessengerTalk $talk, User $sender, string $content): void
    {
        $message = new MessengerMessage();
        $message->setContent($content)
            ->setSender($sender)
            ->setTalk($talk);

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}
