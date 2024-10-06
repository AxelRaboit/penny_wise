<?php

declare(strict_types=1);

namespace App\Service\Voter\Account;

use App\Entity\Account;
use App\Exception\AccountAccessDeniedException;
use App\Exception\MaxAccountsReachedException;
use App\Security\Voter\Account\AccountVoter;
use App\Service\User\UserCheckerService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class AccountVoterService
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private UserCheckerService $userCheckerService
    ) {}

    public function canCreateAccount(): bool
    {
        $user = $this->userCheckerService->getUserOrThrow();
        if (!$this->authorizationChecker->isGranted(AccountVoter::CREATE_ACCOUNT, $user)) {
            throw new MaxAccountsReachedException();
        }

        return true;
    }

    public function canAccessAccount(Account $account): void
    {
        if (!$this->authorizationChecker->isGranted(AccountVoter::ACCESS_ACCOUNT, $account)) {
            throw new AccountAccessDeniedException();
        }
    }
}
