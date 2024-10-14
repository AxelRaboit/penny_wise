<?php

declare(strict_types=1);

namespace App\Manager\AccountList\Wallet;

use App\Entity\Account;
use App\Entity\Wallet;
use App\Service\User\UserCheckerService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

final readonly class AccountListWalletCreationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserCheckerService $userCheckerService,
    ) {}

    public function beginWalletCreation(Account $account): Wallet
    {
        return (new Wallet())
            ->setAccount($account);
    }

    public function beginWalletYearCreation(Account $account, int $year): Wallet
    {
        $wallet = $this->beginWalletCreation($account);
        $wallet->setYear($year);

        return $wallet;
    }

    public function beginWalletYearCreationWithMonth(Account $account, int $year, int $month): Wallet
    {
        $wallet = $this->beginWalletYearCreation($account, $year);
        $wallet->setMonth($month);

        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
        if (false === $startDate) {
            throw new RuntimeException(sprintf('Invalid date format for year: %d, month: %d', $year, $month));
        }

        $endDate = $startDate->modify('last day of this month');

        $wallet->setStartDate($startDate);
        $wallet->setEndDate($endDate);

        return $wallet;
    }

    public function endWalletCreation(Wallet $wallet): void
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $wallet->setIndividual($user);
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }
}
