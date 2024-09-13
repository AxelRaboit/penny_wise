<?php

namespace App\Entity;

use DateTimeInterface;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\MonthEnum;
use App\Repository\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'budget_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private User $individual;

    #[ORM\Column]
    private int $year;

    #[ORM\Column]
    private int $month;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private DateTimeInterface $startDate;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private DateTimeInterface $endDate;

    #[ORM\Column(length: 255)]
    private string $currency;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string|float|null $startBalance = 0.0;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'budget')]
    private Collection $transactions;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string|float|null $remainingBalance = 0.0;

    /**
     * @var Collection<int, Note>
     */
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'budget')]
    private Collection $notes;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndividual(): User
    {
        return $this->individual;
    }

    public function setIndividual(User $individual): static
    {
        $this->individual = $individual;

        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(MonthEnum $month): static
    {
        $this->month = $month->value;

        return $this;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStartBalance(): float
    {
        return (float) ($this->startBalance ?? 0);
    }

    public function setStartBalance(string|float $startBalance): static
    {
        $this->startBalance = $startBalance;

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
            $transaction->setBudget($this);
        }

        return $this;
    }

    public function getMonthLabel(): string
    {
        return $this->startDate->format('F');
    }

    public function getRemainingBalance(): string|float|null
    {
        return $this->remainingBalance;
    }

    public function setRemainingBalance(string|float $remainingBalance): static
    {
        $this->remainingBalance = $remainingBalance;

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setBudget($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        // set the owning side to null (unless already changed)
        if ($this->notes->removeElement($note) && $note->getBudget() === $this) {
            $note->setBudget(null);
        }

        return $this;
    }
}
