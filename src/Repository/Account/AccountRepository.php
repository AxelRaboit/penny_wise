<?php

declare(strict_types=1);

namespace App\Repository\Account;

use App\Entity\Account;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
final class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * Find all accounts with wallets associated to a specific user.
     *
     * @return Account[]
     */
    public function findAllAccountsWithWalletsByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.wallets', 'w')
            ->addSelect('w')
            ->where('a.individual = :user')
            ->setParameter('user', $user)
            ->getQuery();

        /** @var Account[] $accounts */
        $accounts = $qb->getResult();

        return $accounts;
    }

    public function findSpecificAccountByUser(User $user, int $accountId): ?Account
    {
        /** @var Account|null $account */
        $account = $this->createQueryBuilder('a')
            ->andWhere('a.id = :accountId')
            ->andWhere('a.individual = :user')
            ->setParameter('accountId', $accountId)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $account;
    }
}
