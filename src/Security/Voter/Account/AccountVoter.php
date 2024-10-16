<?php

declare(strict_types=1);

namespace App\Security\Voter\Account;

use App\Entity\Account;
use App\Entity\User;
use App\Exception\MaxAccountsReachedException;
use App\Repository\Account\AccountRepository;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Account|null>
 */
class AccountVoter extends Voter
{
    private const int MAX_ACCOUNTS = 3;

    public const string ACCESS_ACCOUNT = 'ACCESS_ACCOUNT';

    public const string CREATE_ACCOUNT = 'CREATE_ACCOUNT';

    public function __construct(private readonly AccountRepository $accountRepository) {}

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::CREATE_ACCOUNT === $attribute) {
            return true;
        }

        return self::ACCESS_ACCOUNT === $attribute && $subject instanceof Account;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Account|null $account */
        $account = $subject;

        return match ($attribute) {
            self::CREATE_ACCOUNT => $this->canCreateAccount($user),
            self::ACCESS_ACCOUNT => $account instanceof Account && $this->canAccessAccount($user, $account),
            default => false,
        };
    }

    private function canCreateAccount(User $user): bool
    {
        $existingAccounts = $this->accountRepository->count(['user' => $user]);
        if ($existingAccounts >= self::MAX_ACCOUNTS) {
            throw new MaxAccountsReachedException();
        }

        return true;
    }

    private function canAccessAccount(User $user, Account $account): bool
    {
        /** @var int $accountId */
        $accountId = $account->getId();

        $userAccount = $this->accountRepository->findSpecificAccountByUser($user, $accountId);

        return $userAccount instanceof Account;
    }
}
