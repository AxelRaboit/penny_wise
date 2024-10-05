<?php

declare(strict_types=1);

namespace App\Service\Account;

use App\Entity\Account;
use App\Repository\Account\AccountRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AccountGetOrThrowService
{
    public function __construct(private AccountRepository $accountRepository) {}

    public function get(int $id): Account
    {
        $account = $this->accountRepository->find($id);
        if (!$account instanceof Account) {
            throw new NotFoundHttpException('Account not found');
        }

        return $account;
    }
}
