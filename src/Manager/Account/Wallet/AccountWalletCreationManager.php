<?php

declare(strict_types=1);

namespace App\Manager\Account\Wallet;

use App\Entity\Account;
use App\Entity\Wallet;
use App\Service\User\UserCheckerService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AccountWalletCreationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserCheckerService $userCheckerService,
    ) {}

    public function beginWalletYearCreation(Account $account, int $year): Wallet
    {
        $wallet = $this->beginWalletCreation($account);
        $wallet->setYear($year);

        return $wallet;
    }

    public function beginWalletCreation(Account $account): Wallet
    {
        return (new Wallet())->setAccount($account);
    }

    public function endWalletCreation(Wallet $wallet): void
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $wallet->setUser($user);
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }
}
