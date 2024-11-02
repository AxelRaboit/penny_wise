<?php

namespace App\Manager\Messenger;

use App\Entity\MessengerMessage;
use App\Entity\MessengerTalk;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MessengerManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Ajoute un message dans une conversation.
     *
     * @param MessengerTalk $talk
     * @param User $sender
     * @param string $content
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
