<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Wallet;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Find transactions by wallet and transaction category.
     *
     * @return Transaction[] returns an array of Transaction objects
     */
    public function findTransactionsByWalletAndCategory(Wallet $wallet, int $transactionCategoryId): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->where('t.wallet = :wallet')
            ->andWhere('t.transactionCategory = :transactionCategory')
            ->setParameter('wallet', $wallet)
            ->setParameter('transactionCategory', $transactionCategoryId)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Retrieve transactions associated with a specific wallet.
     *
     * @return Transaction[] returns an array of Transaction objects
     */
    public function findTransactionsByWallet(Wallet $wallet): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->where('t.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
