<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'transaction_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string|float $amount;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private Wallet $wallet;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private TransactionCategory $transactionCategory;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nature = null;

    /**
     * @var Collection<int, TransactionTag>
     */
    #[ORM\ManyToMany(targetEntity: TransactionTag::class, inversedBy: 'transactions')]
    private Collection $tag;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $budget = null;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $budgetDefinedTroughAmount = true;

    public function __construct()
    {
        $this->tag = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): string|float
    {
        return $this->amount;
    }

    public function setAmount(string|float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function setWallet(Wallet $wallet): static
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getTransactionCategory(): TransactionCategory
    {
        return $this->transactionCategory;
    }

    public function setTransactionCategory(TransactionCategory $transactionCategory): static
    {
        $this->transactionCategory = $transactionCategory;

        return $this;
    }

    public function getNature(): ?string
    {
        return $this->nature;
    }

    public function setNature(?string $nature): static
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * @return Collection<int, TransactionTag>
     */
    public function getTag(): Collection
    {
        return $this->tag;
    }

    public function addTag(TransactionTag $tag): static
    {
        if (!$this->tag->contains($tag)) {
            $this->tag->add($tag);
        }

        return $this;
    }

    public function removeTag(TransactionTag $tag): static
    {
        $this->tag->removeElement($tag);

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(?string $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getBudgetDefinedTroughAmount(): ?bool
    {
        return $this->budgetDefinedTroughAmount;
    }

    public function setBudgetDefinedTroughAmount(?bool $budgetDefinedTroughAmount): self
    {
        $this->budgetDefinedTroughAmount = $budgetDefinedTroughAmount;

        return $this;
    }
}
