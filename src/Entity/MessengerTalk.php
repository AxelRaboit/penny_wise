<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'messenger_talk')]
class MessengerTalk
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'messenger_talk_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * @var Collection<int, MessengerParticipant>
     */
    #[ORM\OneToMany(mappedBy: 'talk', targetEntity: MessengerParticipant::class, cascade: ['persist', 'remove'])]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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
}
