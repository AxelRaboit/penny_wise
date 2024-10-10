<?php

declare(strict_types=1);

namespace App\Repository\Wallet;

use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\Transaction\TransactionCategoryEnum;
use App\Enum\Wallet\MonthEnum;
use App\Repository\Transaction\TransactionCategoryRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
final class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly TransactionCategoryRepository $transactionCategoryRepository)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * Fetches the total spending for each year within a specified range, excluding the income category.
     *
     * @param int $startYear        the starting year of the range
     * @param int $endYear          the ending year of the range
     * @param int $incomeCategoryId the ID of the income category to exclude
     * @param int $accountId        the ID of the account for which the data is fetched
     *
     * @return array<array{year: int, total: float}> an array containing the total spending for each year within the specified range
     */
    public function fetchTotalSpendingPerYear(int $startYear, int $endYear, int $incomeCategoryId, int $accountId): array
    {
        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.transactions', 't')
            ->leftJoin('t.transactionCategory', 'tc')
            ->leftJoin('w.account', 'a')
            ->select('w.year, COALESCE(SUM(t.amount), 0) as total')
            ->where('w.year BETWEEN :startYear AND :endYear')
            ->andWhere('tc.id != :incomeCategoryId')
            ->andWhere('a.id = :accountId')
            ->setParameter('startYear', $startYear)
            ->setParameter('endYear', $endYear)
            ->setParameter('incomeCategoryId', $incomeCategoryId)
            ->setParameter('accountId', $accountId)
            ->groupBy('w.year')
            ->orderBy('w.year', 'ASC')
            ->getQuery();

        /** @var array<array{year: int, total: float}> $results */
        $results = $qb->getArrayResult();

        return $results;
    }

    /**
     * Finds and retrieves all wallets associated with a given user.
     *
     * @param User $user the user whose wallets are to be fetched
     *
     * @return Wallet[] the list of wallets associated with the specified user
     */
    public function findAllWalletByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('w')
            ->where('w.individual = :user')
            ->setParameter('user', $user);

        /** @var Wallet[] $wallets */
        $wallets = $qb->getQuery()->getResult();

        return $wallets;
    }

    /**
     * Checks if the given user has an associated wallet.
     *
     * @param User $user the user to be checked for an associated wallet
     *
     * @return bool true if the user has a wallet, false otherwise
     */
    public function userHasWallet(User $user): bool
    {
        $qb = $this->createQueryBuilder('w')
            ->select('1')
            ->where('w.individual = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1);

        return (bool) $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Finds a specific wallet by a given user and wallet ID.
     *
     * @param User $user the user associated with the wallet
     * @param int  $id   the identifier of the wallet to be found
     *
     * @return Wallet|null the wallet if found, null otherwise
     */
    public function findSpecificWalletByUser(User $user, int $id): ?Wallet
    {
        $qb = $this->createQueryBuilder('w')
            ->where('w.individual = :user')
            ->andWhere('w.id = :id')
            ->setParameter('user', $user)
            ->setParameter('id', $id);

        $wallet = $qb->getQuery()->getOneOrNullResult();

        return $wallet instanceof Wallet ? $wallet : null;
    }

    /**
     * Retrieves the total spending for multiple months, grouped by year, month, and account ID, excluding incomes.
     *
     * @param array<int, array{year: int, month: int, accountId: int}> $monthsData an array of associative arrays,
     *                                                                             where each associative array contains keys 'year', 'month', and 'accountId'
     *
     * @return array<int, array{year: int, month: int, total: float}> an array of results, each containing the year,
     *                                                                month, and the total spending for that period
     */
    public function getTotalSpendingForMonths(array $monthsData): array
    {
        $incomeCategory = $this->transactionCategoryRepository->findOneBy(['name' => TransactionCategoryEnum::Incomes->value]);
        if (null === $incomeCategory) {
            throw new LogicException('Income category not found.');
        }

        $incomeCategoryId = $incomeCategory->getId();

        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.transactions', 't')
            ->leftJoin('t.transactionCategory', 'tc')
            ->leftJoin('w.account', 'a')
            ->select('w.year, w.month, a.id as accountId, COALESCE(SUM(t.amount), 0) as total')
            ->where('tc.id != :incomeCategoryId')
            ->groupBy('w.year, w.month, a.id')
            ->setParameter('incomeCategoryId', $incomeCategoryId);

        $orX = $qb->expr()->orX();

        foreach ($monthsData as $index => $monthData) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('w.year', ':year'.$index),
                    $qb->expr()->eq('w.month', ':month'.$index),
                    $qb->expr()->eq('a.id', ':accountId'.$index)
                )
            );
            $qb->setParameter('year'.$index, $monthData['year']);
            $qb->setParameter('month'.$index, $monthData['month']);
            $qb->setParameter('accountId'.$index, $monthData['accountId']);
        }

        $qb->andWhere($orX);

        /** @var array<int, array{year: int, month: int, total: float}> $result */
        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * Finds a wallet by account ID, year, and month.
     *
     * @param int $accountId the identifier of the account associated with the wallet
     * @param int $year      the year the wallet is associated with
     * @param int $month     the month the wallet is associated with
     *
     * @return Wallet|null the wallet if found, null otherwise
     */
    public function findWalletByAccountYearAndMonth(int $accountId, int $year, int $month): ?Wallet
    {
        $result = $this->createQueryBuilder('w')
            ->where('w.account = :accountId')
            ->andWhere('w.year = :year')
            ->andWhere('w.month = :month')
            ->setParameter('accountId', $accountId)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof Wallet ? $result : null;
    }

    /**
     * Retrieves all wallets and their associated transactions for a given account and year.
     *
     * @param int $accountId the ID of the account
     * @param int $year      the year for which the wallets and transactions are retrieved
     *
     * @return array<Wallet> the list of wallets and their transactions
     */
    public function getAllWalletsAndTransactionsByAccountAndYear(int $accountId, int $year): array
    {
        /** @var Wallet[] $results */
        $results = $this->createQueryBuilder('w')
            ->leftJoin('w.transactions', 't')
            ->addSelect('t')
            ->where('w.account = :accountId')
            ->andWhere('w.year = :year')
            ->setParameter('accountId', $accountId)
            ->setParameter('year', $year)
            ->orderBy('w.month', Order::Ascending->value)
            ->getQuery()
            ->getResult();

        return $results;
    }

    /**
     * Finds wallets by account ID and year.
     *
     * @param int $accountId The account ID
     * @param int $year      The year to search for wallets
     *
     * @return Wallet[] The wallets associated with the specified year and account
     */
    public function findWalletsByAccountAndYear(int $accountId, int $year): array
    {
        /** @var Wallet[] $wallets */
        $wallets = $this->createQueryBuilder('w')
            ->where('w.account = :accountId')
            ->andWhere('w.year = :year')
            ->setParameter('accountId', $accountId)
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult();

        return $wallets;
    }

    /**
     * Finds the wallet for a specific user, given the year and month.
     *
     * @param User $user  the user for whom the monthly wallet is being searched
     * @param int  $year  the year for which the wallet is searched
     * @param int  $month the month for which the wallet is searched
     *
     * @return Wallet|null the found Wallet entity, or null if no wallet is found
     */
    public function findWalletByUser(User $user, int $year, int $month): ?Wallet
    {
        // TODO AXEL: use account ID instead of individual (check if it's possible for each usage)

        $month = MonthEnum::from($month);

        $wallet = $this->createQueryBuilder('w')
            ->where('w.individual = :user')
            ->andWhere('w.year = :year')
            ->andWhere('w.month = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $wallet instanceof Wallet ? $wallet : null;
    }

    public function findLastWalletByAccountAndYear(int $accountId, int $year): ?Wallet
    {
        $result = $this->createQueryBuilder('w')
            ->where('w.account = :accountId')
            ->andWhere('w.year = :year')
            ->setParameter('accountId', $accountId)
            ->setParameter('year', $year)
            ->orderBy('w.month', Order::Descending->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof Wallet ? $result : null;
    }
}
