<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use DateInterval;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'login_attempt')]
class LoginAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'login_attempt_id_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $attempts = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastAttemptAt = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isBlocked = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $blockedUntil = null;

    /**
     * @throws DateMalformedStringException
     */
    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly User $user
    ) {
        $this->incrementAttempts();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function incrementAttempts(): void
    {
        ++$this->attempts;
        $this->lastAttemptAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public function resetAttempts(): void
    {
        $this->attempts = 0;
        $this->isBlocked = false;
        $this->blockedUntil = null;
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedIntervalStringException
     */
    public function block(int $durationInMinutes): void
    {
        $this->isBlocked = true;
        $this->blockedUntil = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->add(new DateInterval(sprintf('PT%dM', $durationInMinutes)));
    }

    public function unblock(): void
    {
        $this->resetAttempts();
    }

    public function setLastAttemptAt(DateTimeImmutable $lastAttemptAt): void
    {
        $this->lastAttemptAt = $lastAttemptAt;
    }

    public function getLastAttemptAt(): ?DateTimeImmutable
    {
        return $this->lastAttemptAt;
    }

    public function setBlockedUntil(?DateTimeImmutable $blockedUntil): void
    {
        $this->blockedUntil = $blockedUntil;
    }

    public function getBlockedUntil(): ?DateTimeImmutable
    {
        return $this->blockedUntil;
    }

    public function setIsBlocked(bool $isBlocked): void
    {
        $this->isBlocked = $isBlocked;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }
}
