<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'messenger_talk')]
class MessengerTalk
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'messenger_talk_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var Collection<int, MessengerParticipant>
     */
    #[ORM\OneToMany(targetEntity: MessengerParticipant::class, mappedBy: 'talk', cascade: ['persist', 'remove'])]
    private Collection $participants;

    /**
     * @var Collection<int, MessengerMessage>
     */
    #[ORM\OneToMany(targetEntity: MessengerMessage::class, mappedBy: 'talk', cascade: ['persist', 'remove'])]
    private Collection $messages;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, MessengerParticipant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(MessengerParticipant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setTalk($this);
        }

        return $this;
    }

    public function removeParticipant(MessengerParticipant $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    /**
     * @return Collection<int, MessengerMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(MessengerMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setTalk($this);
        }

        return $this;
    }

    public function removeMessage(MessengerMessage $message): self
    {
        $this->messages->removeElement($message);

        return $this;
    }
}
