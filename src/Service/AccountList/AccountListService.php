<?php

declare(strict_types=1);

namespace App\Service\AccountList;

use App\Entity\Account;
use App\Service\Account\AccountService;
use App\Service\User\UserCheckerService;
use Random\RandomException;

final readonly class AccountListService
{
    public function __construct(
        private UserCheckerService $userCheckerService,
        private AccountService $accountService
    ) {}

    /**
     * @throws RandomException
     */
    public function beginAccountCreation(): Account
    {
        $account = new Account();
        $account->setUser($this->userCheckerService->getUserOrThrow());
        $account->setIdentifier($this->accountService->generateUniqueIdentifier());

        return $account;
    }
}
