<?php

declare(strict_types=1);

namespace App\Manager\Messenger;

use App\Entity\Friendship;
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
            $messenger = $participant->getMessenger();
            $user = $messenger?->getUser();
            if ($user && $user !== $sender && !$participant->isVisibleToParticipant()) {
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

    public function createOrReactivateTalk(User $user, User $friend): MessengerTalk
    {
        // Vérifie si une conversation existante masquée existe déjà
        $existingTalk = $this->messengerTalkRepository->findExistingTalk($user, $friend);

        if ($existingTalk) {
            foreach ($existingTalk->getParticipants() as $participant) {
                if ($participant->getMessenger()->getUser() === $user && !$participant->isVisibleToParticipant()) {
                    // Réactive la conversation en la rendant visible
                    $participant->setVisibleToParticipant(true);
                    $this->entityManager->persist($participant);
                }
            }
            $this->entityManager->flush();
            return $existingTalk;
        }

        // Crée une nouvelle conversation si aucune existante n'a été trouvée
        return $this->createTalk($user, $friend);
    }


    /**
     * @return array<User>
     */
    public function getFriendsForNewConversation(User $user): array
    {
        // Récupère tous les amis de l'utilisateur
        $friends = $user->getAcceptedFriends()->map(fn (Friendship $friendship): ?User => $friendship->getFriend())->toArray();

        // Récupère les IDs des amis pour qui une conversation visible existe déjà
        $visibleTalkFriendIds = [];
        foreach ($this->getTalksForUser($user) as $talk) {
            foreach ($talk->getParticipants() as $participant) {
                if ($participant->getMessenger()->getUser() !== $user) {
                    $visibleTalkFriendIds[] = $participant->getMessenger()->getUser()->getId();
                }
            }
        }

        // Inclut les amis même avec conversations masquées
        return array_filter($friends, fn (User $friend): bool => !in_array($friend->getId(), $visibleTalkFriendIds, true));
    }



    public function hideTalkForUser(MessengerTalk $talk, User $user): void
    {
        foreach ($talk->getParticipants() as $participant) {
            if ($participant->getMessenger()->getUser() === $user) {
                $participant->setVisibleToParticipant(false);
                $this->entityManager->persist($participant);
            }
        }
        $this->entityManager->flush();
    }

    public function createOrReopenTalk(User $user, User $friend): MessengerTalk
    {
        // Cherche une conversation existante, qu'elle soit visible ou masquée
        $existingTalk = $this->messengerTalkRepository->findExistingOrHiddenTalk($user, $friend);

        if ($existingTalk) {
            // Si une conversation masquée existe, rendez-la visible pour l'utilisateur
            foreach ($existingTalk->getParticipants() as $participant) {
                if ($participant->getMessenger()->getUser() === $user && !$participant->isVisibleToParticipant()) {
                    $participant->setVisibleToParticipant(true);
                    $this->entityManager->persist($participant);
                }
            }
            $this->entityManager->flush();

            return $existingTalk;
        }

        // Sinon, crée une nouvelle conversation
        return $this->createTalk($user, $friend);
    }

}
