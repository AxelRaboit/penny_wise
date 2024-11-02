<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'messenger_participant')]
class MessengerParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'messenger_participant_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MessengerTalk::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MessengerTalk $talk = null;

    #[ORM\ManyToOne(targetEntity: Messenger::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Messenger $messenger = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isDeleted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTalk(): ?MessengerTalk
    {
        return $this->talk;
    }

    public function setTalk(MessengerTalk $talk): self
    {
        $this->talk = $talk;

        return $this;
    }

    public function getMessenger(): ?Messenger
    {
        return $this->messenger;
    }

    public function setMessenger(Messenger $messenger): self
    {
        $this->messenger = $messenger;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->isDeleted = $deleted;

        return $this;
    }
}
