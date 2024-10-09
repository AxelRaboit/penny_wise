<?php

declare(strict_types=1);

namespace App\Service\Voter\Account;

use App\Entity\Account;
use App\Security\Voter\Account\AccountVoter;
use App\Service\User\UserCheckerService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class AccountVoterService
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private UserCheckerService $userCheckerService
    ) {}

    /**
     * Check if the user can create an account.
     *
     * @return bool returns true if the user can create an account, false otherwise
     */
    public function canCreateAccount(): bool
    {
        $user = $this->userCheckerService->getUserOrThrow();

        return $this->authorizationChecker->isGranted(AccountVoter::CREATE_ACCOUNT, $user);
    }

    /**
     * Check if the user can access the account.
     *
     * @return bool returns true if the user can access the account, false otherwise
     */
    public function canAccessAccount(Account $account): bool
    {
        return $this->authorizationChecker->isGranted(AccountVoter::ACCESS_ACCOUNT, $account);
    }
}
