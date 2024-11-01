<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Entity\User;
use App\Manager\Security\LoginAttempt\LoginAttemptManager;
use App\Repository\Security\LoginAttemptRepository;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;

final readonly class LoginAttemptService
{
    private const int MAX_ATTEMPTS = 5;

    private const int LOCK_TIME = 30;

    public function __construct(
        private LoginAttemptManager $loginAttemptManager,
        private LoginAttemptRepository $loginAttemptRepository,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedIntervalStringException
     */
    public function recordFailedAttempt(User $user): void
    {
        $attempt = $this->loginAttemptManager->findOrCreateByUser($user);
        if ($attempt->isBlocked()) {
            return;
        }

        $this->loginAttemptManager->incrementAttempts($attempt);
        if ($attempt->getAttempts() >= self::MAX_ATTEMPTS) {
            $attempt->block(self::LOCK_TIME);
        }

        $this->loginAttemptManager->save($attempt);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function isBlocked(User $user): bool
    {
        $loginAttempt = $this->loginAttemptManager->findOrCreateByUser($user);
        $currentDateTime = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
        $blockedUntil = $loginAttempt->getBlockedUntil();
        $isCurrentlyBlocked = $loginAttempt->isBlocked();

        if ($isCurrentlyBlocked && $blockedUntil instanceof DateTimeImmutable && $currentDateTime < $blockedUntil) {
            return true;
        }

        if ($isCurrentlyBlocked && $currentDateTime >= $blockedUntil) {
            $this->loginAttemptManager->resetAttempts($user);
            $this->loginAttemptRepository->deleteByUser($user);
        }

        return false;
    }
}
