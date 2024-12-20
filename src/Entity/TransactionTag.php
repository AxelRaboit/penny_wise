<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Transaction\TransactionTagRepository;
use App\Validator\TransactionTag\TransactionTagMaxTransactionTags;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionTagRepository::class)]
#[TransactionTagMaxTransactionTags]
class TransactionTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'transaction_tag_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'transactionTags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\ManyToMany(targetEntity: Transaction::class, mappedBy: 'tag')]
    private Collection $transactions;

    #[ORM\Column(length: 7)]
    private ?string $color = '#ffffff';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
    private ?bool $useDefaultColor = true;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->addTag($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            $transaction->removeTag($this);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function isUseDefaultColor(): ?bool
    {
        return $this->useDefaultColor;
    }

    public function setUseDefaultColor(bool $useDefaultColor): static
    {
        $this->useDefaultColor = $useDefaultColor;

        return $this;
    }
}
