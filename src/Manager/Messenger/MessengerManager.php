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

    public function addMessage(MessengerTalk $talk, User $sender, string $content): void
    {
        $message = new MessengerMessage();
        $message->setContent($content)
            ->setSender($sender)
            ->setTalk($talk);

        // Vérifier si le destinataire est déjà participant du talk
        $participant = $this->getTalkParticipant($talk, $sender);
        if (!$participant) {
            $recipientMessenger = $this->getRecipientMessenger($talk, $sender);
            if ($recipientMessenger) {
                $friendParticipant = new MessengerParticipant();
                $friendParticipant->setTalk($talk)->setMessenger($recipientMessenger);
                $this->entityManager->persist($friendParticipant);
            }
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }



    public function createTalk(User $user, User $friend): MessengerTalk
    {
        $talk = new MessengerTalk();

        // Ajouter l'utilisateur comme participant
        $userParticipant = new MessengerParticipant();
        $userParticipant->setTalk($talk)->setMessenger($user->getMessenger());
        $this->entityManager->persist($userParticipant);

        // Ajouter l'ami comme participant
        $friendParticipant = new MessengerParticipant();
        $friendParticipant->setTalk($talk)->setMessenger($friend->getMessenger());
        $this->entityManager->persist($friendParticipant);

        // Persister la conversation
        $this->entityManager->persist($talk);
        $this->entityManager->flush();

        return $talk;
    }


    public function getRecipientMessenger(MessengerTalk $talk, User $sender): ?Messenger
    {
        foreach ($talk->getParticipants() as $participant) {
            $messenger = $participant->getMessenger();
            if ($messenger && $messenger->getUser() !== $sender) {
                return $messenger;
            }
        }

        return null;
    }

    public function getFriendsForNewConversation(User $user): array
    {
        // Récupérer tous les amis de l'utilisateur
        $friends = $user->getAcceptedFriends()->map(fn(Friendship $friendship) => $friendship->getFriend())->toArray();

        // Récupérer les IDs des utilisateurs avec lesquels une conversation existe déjà
        $existingTalkFriendIds = [];
        foreach ($this->getTalksForUser($user) as $talk) {
            foreach ($talk->getParticipants() as $participant) {
                if ($participant->getMessenger()->getUser() !== $user) {
                    $existingTalkFriendIds[] = $participant->getMessenger()->getUser()->getId();
                }
            }
        }

        // Filtrer les amis pour exclure ceux qui ont déjà une conversation
        return array_filter($friends, fn(User $friend) => !in_array($friend->getId(), $existingTalkFriendIds, true));
    }





}
