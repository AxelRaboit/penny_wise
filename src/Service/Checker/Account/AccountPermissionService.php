<?php

declare(strict_types=1);

namespace App\Service\Checker\Account;

use App\Exception\MaxAccountsReachedException;
use App\Service\Voter\Account\AccountVoterService;

final readonly class AccountPermissionService
{
    public function __construct(
        private AccountVoterService $accountVoterService
    ) {}

    /**
     * Check if the user can create an account.
     *
     * @return bool returns true if the user can create an account, false otherwise
     *
     * @throws MaxAccountsReachedException
     */
    public function checkAccountCreationPermissions(): bool
    {
        if (!$this->accountVoterService->canCreateAccount()) {
            throw new MaxAccountsReachedException('Maximum number of accounts reached.');
        }

        return true;
    }
}
