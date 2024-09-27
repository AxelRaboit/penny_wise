<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
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
     * @return Transaction[] Returns an array of Transaction objects
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
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findTransactionsByWallet(Wallet $wallet): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->where('t.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Retrieve transactions associated with a specific wallet, along with their related transaction category and tags.
     *
     * @return Transaction[] Returns an array of Transaction objects with related entities
     */
    public function findTransactionsByWalletWithRelations(Wallet $wallet): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->leftJoin('t.transactionCategory', 'tc')
            ->addSelect('tc')
            ->leftJoin('t.tag', 'tg')
            ->addSelect('tg')
            ->where('t.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->orderBy('t.highlight', Order::Descending->value)
            ->addOrderBy('t.id', Order::Ascending->value)
            ->getQuery()
            ->getResult();

        return $result;
    }


    /**
     * Return all transactions of a specific category for a given wallet.
     *
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findTransactionsByCategory(Wallet $wallet, string $category): array
    {
        /** @var Transaction[] $result */
        $result = $this->createQueryBuilder('t')
            ->join('t.transactionCategory', 'tc')
            ->where('t.wallet = :wallet')
            ->andWhere('LOWER(tc.name) = LOWER(:category)')
            ->setParameter('wallet', $wallet)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
