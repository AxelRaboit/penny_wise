<?php

declare(strict_types=1);

namespace App\Service\Checker\Account;

use App\Entity\Account;
use App\Repository\Account\AccountRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AccountCheckerService
{
    public function __construct(private AccountRepository $accountRepository) {}

    /**
     * Returns the account or throws an exception if not found.
     */
    public function getAccountOrThrow(int $id): Account
    {
        $account = $this->accountRepository->find($id);
        if (!$account instanceof Account) {
            throw new NotFoundHttpException('Account not found');
        }

        return $account;
    }

    /**
     * Returns the account or null if not found.
     */
    public function getAccountOrNull(int $id): ?Account
    {
        return $this->accountRepository->find($id);
    }
}
