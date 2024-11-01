<?php

declare(strict_types=1);

namespace App\Manager\Security\LoginAttempt;

use App\Entity\LoginAttempt;
use App\Entity\User;
use App\Repository\Security\LoginAttemptRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

class LoginAttemptManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoginAttemptRepository $loginAttemptRepository
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function findOrCreateByUser(User $user): LoginAttempt
    {
        /** @var LoginAttempt|null $loginAttempt */
        $loginAttempt = $this->loginAttemptRepository->findOneBy(['user' => $user]);
        if (null === $loginAttempt) {
            $loginAttempt = new LoginAttempt($user);
            $this->save($loginAttempt);
        }

        return $loginAttempt;
    }

    public function save(LoginAttempt $loginAttempt): void
    {
        $this->entityManager->persist($loginAttempt);
        $this->entityManager->flush();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function resetAttempts(User $user): void
    {
        $attempt = $this->findOrCreateByUser($user);
        $attempt->unblock();

        $this->save($attempt);
    }

    /**
     * Increments the login attempt count and updates the last attempt time.
     *
     * @throws DateMalformedStringException
     */
    public function incrementAttempts(LoginAttempt $loginAttempt): void
    {
        $loginAttempt->setAttempts($loginAttempt->getAttempts() + 1);
        $loginAttempt->setLastAttemptAt(new DateTimeImmutable('now', new DateTimeZone('UTC')));

        $this->save($loginAttempt);
    }
}
