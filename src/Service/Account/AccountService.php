<?php

declare(strict_types=1);

namespace App\Service\Account;

use App\Repository\Account\AccountRepository;
use App\Service\User\UserCheckerService;
use Random\RandomException;

final readonly class AccountService
{
    public function __construct(
        private AccountRepository $accountRepository,
        private UserCheckerService $userCheckerService
    ) {}

    /**
     * @throws RandomException
     */
    public function generateUniqueIdentifier(): string
    {
        $user = $this->userCheckerService->getUserOrThrow();
        $userId = $user->getId();

        do {
            $identifier = sprintf('%d-%s-%d', $userId, date('YmdHis'), random_int(1000, 9999));
        } while ($this->accountRepository->findOneBy(['identifier' => $identifier, 'user' => $userId]));

        return $identifier;
    }
}
