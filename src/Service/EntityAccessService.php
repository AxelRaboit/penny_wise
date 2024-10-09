<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Service\Checker\Account\AccountCheckerService;
use App\Service\Checker\Transaction\TransactionCheckerService;
use App\Service\Checker\Wallet\WalletCheckerService;
use App\Service\Voter\Account\AccountVoterService;
use App\Service\Voter\Account\Wallet\Transaction\TransactionVoterService;
use App\Service\Voter\Account\Wallet\WalletVoterService;
use Exception;

final readonly class EntityAccessService
{
    public function __construct(
        private AccountCheckerService $accountCheckerService,
        private WalletCheckerService $walletCheckerService,
        private TransactionCheckerService $transactionCheckerService,
        private AccountVoterService $accountVoterService,
        private WalletVoterService $walletVoterService,
        private TransactionVoterService $transactionVoterService
    ) {}

    public function getAccountWithAccessCheck(int $accountId): ?Account
    {
        $account = $this->getEntityWithAccessCheck(
            $accountId,
            fn ($id): ?Account => $this->accountCheckerService->getAccountOrNull($id),
            fn (?object $account): bool => $account instanceof Account && $this->accountVoterService->canAccessAccount($account)
        );

        return $account instanceof Account ? $account : null;
    }

    public function getWalletWithAccessCheck(int $walletId): ?Wallet
    {
        $wallet = $this->getEntityWithAccessCheck(
            $walletId,
            fn ($id): ?Wallet => $this->walletCheckerService->getWalletByIdOrNull($id),
            fn (?object $wallet): bool => $wallet instanceof Wallet && $this->walletVoterService->canAccessWallet($wallet)
        );

        return $wallet instanceof Wallet ? $wallet : null;
    }

    public function getTransactionWithAccessCheck(int $transactionId): ?Transaction
    {
        $transaction = $this->getEntityWithAccessCheck(
            $transactionId,
            fn ($id): ?Transaction => $this->transactionCheckerService->getTransactionOrNull($id),
            fn (?object $transaction): bool => $transaction instanceof Transaction && $this->transactionVoterService->canAccessTransaction($transaction)
        );

        return $transaction instanceof Transaction ? $transaction : null;
    }

    /**
     * @template T of object
     *
     * @param callable(int): ?T  $getEntityFunction
     * @param callable(?T): bool $accessCheckFunction
     */
    private function getEntityWithAccessCheck(int $entityId, callable $getEntityFunction, callable $accessCheckFunction): ?object
    {
        try {
            /** @var ?T $entity */
            $entity = $getEntityFunction($entityId);
            if (null !== $entity && !$accessCheckFunction($entity)) {
                return null;
            }

            return $entity;
        } catch (Exception) {
            return null;
        }
    }
}
