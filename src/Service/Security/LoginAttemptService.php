<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Entity\LoginAttempt;
use App\Entity\User;
use App\Repository\Security\LoginAttemptRepository;
use DateMalformedStringException;
use DateTimeImmutable;

class LoginAttemptService
{
    private const int MAX_ATTEMPTS = 5;

    private const int LOCK_TIME = 30;

    public function __construct(private readonly LoginAttemptRepository $loginAttemptRepository) {}

    /**
     * @throws DateMalformedStringException
     * @throws \DateMalformedIntervalStringException
     */
    public function recordFailedAttempt(User $user): void
    {
        $attempt = $this->loginAttemptRepository->findOrCreateByUser($user);
        if ($attempt->isBlocked()) {
            return;
        }

        $attempt->incrementAttempts();
        if ($attempt->getAttempts() >= self::MAX_ATTEMPTS) {
            $attempt->block(self::LOCK_TIME);
        }

        $this->loginAttemptRepository->save($attempt);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function isBlocked(User $user): bool
    {
        $loginAttempt = $this->loginAttemptRepository->findOrCreateByUser($user);

        $now = new DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        if ($loginAttempt->isBlocked() && $loginAttempt->getBlockedUntil() && $now < $loginAttempt->getBlockedUntil()) {
            return true;
        }

        if ($loginAttempt->isBlocked() && $now >= $loginAttempt->getBlockedUntil()) {
            $this->resetAttempts($user);
        }

        return false;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function resetAttempts(User $user): void
    {
        $attempt = $this->loginAttemptRepository->findOrCreateByUser($user);
        $attempt->unblock();

        $this->loginAttemptRepository->save($attempt);
    }
}
