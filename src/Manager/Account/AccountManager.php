<?php

declare(strict_types=1);

namespace App\Manager\Account;

use App\Entity\Account;
use App\Entity\User;
use App\Repository\Account\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AccountManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AccountRepository $accountRepository,
        private readonly Security $security
    ) {}

    public function createAccount(Account $account): Account
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $account->setIndividual($user);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $account;
    }

    public function updateAccount(Account $account): void
    {
        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }

    public function deleteAccount(int $id): void
    {
        $account = $this->accountRepository->find($id);
        if (!$account instanceof Account) {
            throw new NotFoundHttpException('Account not found');
        }

        foreach ($account->getWallets() as $wallet) {
            foreach ($wallet->getTransactions() as $transaction) {
                $this->entityManager->remove($transaction);
            }

            $this->entityManager->remove($wallet);
        }

        $this->entityManager->remove($account);
        $this->entityManager->flush();
    }
}
