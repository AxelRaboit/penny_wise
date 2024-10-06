<?php

declare(strict_types=1);

namespace App\Manager\Account\Wallet;

use App\Entity\Wallet;
use App\Service\Checker\Account\AccountCheckerService;
use App\Service\User\UserCheckerService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

final readonly class WalletCreationManager
{
    public function __construct(
        private AccountCheckerService $accountCheckerService,
        private EntityManagerInterface $entityManager,
        private UserCheckerService $userCheckerService,
    ) {}

    public function beginWalletCreation(int $accountId): Wallet
    {
        $account = $this->accountCheckerService->getAccountOrThrow($accountId);

        return (new Wallet())
            ->setAccount($account);
    }

    public function beginWalletYearCreation(int $accountId, int $year): Wallet
    {
        $wallet = $this->beginWalletCreation($accountId);
        $wallet->setYear($year);

        return $wallet;
    }

    public function endWalletCreation(Wallet $wallet): void
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $wallet->setIndividual($user);
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }

    public function beginWalletYearCreationWithMonth(int $accountId, int $year, int $month): Wallet
    {
        $wallet = $this->beginWalletYearCreation($accountId, $year);
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
}
