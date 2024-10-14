<?php

declare(strict_types=1);

namespace App\Manager\AccountList;

use App\Entity\Account;
use App\Repository\Wallet\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

final readonly class AccountListWalletManager
{
    public function __construct(
        private WalletRepository $walletRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function createAccount(Account $account): Account
    {
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $account;
    }

    public function updateAccount(Account $account): void
    {
        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }

    public function deleteAccount(Account $account): void
    {
        foreach ($account->getWallets() as $wallet) {
            foreach ($wallet->getTransactions() as $transaction) {
                $this->entityManager->remove($transaction);
            }

            $this->entityManager->remove($wallet);
        }

        $this->entityManager->remove($account);
        $this->entityManager->flush();
    }

    /**
     * Deletes all wallets for a given user for a specific year, along with their associated transactions.
     *
     * @param Account $account The account associated with the wallets
     * @param int     $year    The year for which the wallets are being deleted
     *
     * @throws NotFoundResourceException if no wallets for the given year are found
     */
    public function deleteWalletsForYear(Account $account, int $year): void
    {
        $accountId = $account->getId();
        if (null === $accountId) {
            throw new LogicException('Account ID cannot be null');
        }

        $wallets = $this->walletRepository->findWalletsByAccountAndYear($accountId, $year);

        if ([] === $wallets) {
            throw new NotFoundResourceException('No wallets found for the given year and account.');
        }

        foreach ($wallets as $wallet) {
            foreach ($wallet->getTransactions() as $transaction) {
                $this->entityManager->remove($transaction);
            }

            $this->entityManager->remove($wallet);
        }

        $this->entityManager->flush();
    }
}
