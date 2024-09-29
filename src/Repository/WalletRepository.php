<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\MonthDto;
use App\Dto\YearDto;
use App\Entity\TransactionCategory;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Enum\TransactionCategoryEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly TransactionCategoryRepository $transactionCategoryRepository)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * @return array<array{year: int, month: int}>
     */
    public function findUniqueYearsAndMonthsRawByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('w')
            ->select('w.year, w.month')
            ->where('w.individual = :user')
            ->groupBy('w.year, w.month')
            ->orderBy('w.year', Order::Descending->value)
            ->addOrderBy('w.month', Order::Ascending->value)
            ->setParameter('user', $user);

        $results = $qb->getQuery()->getArrayResult();

        $formattedResults = [];

        foreach ($results as $result) {
            if (is_array($result) && isset($result['year'], $result['month'])) {
                $formattedResults[] = [
                    'year' => (int) $result['year'],
                    'month' => (int) $result['month'],
                ];
            }
        }

        return $formattedResults;
    }

    /**
     * Retrieves the months associated with a specific year.
     *
     * @param int $year The year to filter the months
     *
     * @return YearDto A data transfer object containing the specified year and an array of months
     */
    public function getAllWalletsAndTransactionsFromYear(int $year): YearDto
    {
        $qb = $this->createQueryBuilder('w')
            ->select('w.month')
            ->where('w.year = :year')
            ->setParameter('year', $year)
            ->orderBy('w.month', Order::Ascending->value)
            ->getQuery();

        /** @var array<int, array{month: int}> $months */
        $months = $qb->getArrayResult();

        $result = [];
        foreach ($months as $month) {
            $monthNumber = (int) $month['month'];
            $monthEnum = MonthEnum::from($monthNumber);
            $result[] = new MonthDto($monthEnum->value, $monthEnum->getName());
        }

        return new YearDto($year, $result);
    }

    /**
     * Retrieves the total spending for a given year and month, excluding a specific income category.
     *
     * @param int $year  the year for which the total spending is calculated
     * @param int $month the month for which the total spending is calculated
     *
     * @return float the total spending amount for the specified year and month
     */
    public function getTotalSpendingByMonth(int $year, int $month): float
    {
        $incomeCategory = $this->transactionCategoryRepository->findCategoryByName(TransactionCategoryEnum::Incomes->value);

        if (!$incomeCategory instanceof TransactionCategory) {
            throw new LogicException('Income category not found.');
        }

        $incomeCategoryId = $incomeCategory->getId();

        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.transactions', 't')
            ->leftJoin('t.transactionCategory', 'tc')
            ->select('COALESCE(SUM(t.amount), 0) as totalSpending')
            ->where('w.year = :year')
            ->andWhere('w.month = :month')
            ->andWhere('tc.id != :incomeCategoryId')
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->setParameter('incomeCategoryId', $incomeCategoryId)
            ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

    /**
     * Fetches the total spending for each year within a specified range, excluding the income category.
     *
     * @param int $startYear the starting year of the range
     * @param int $endYear   the ending year of the range
     *
     * @return array<array{year: int, total: float}> an array containing the total spending for each year within the specified range
     */
    public function fetchTotalSpendingPerYear(int $startYear, int $endYear): array
    {
        $incomeCategory = $this->transactionCategoryRepository
            ->findOneBy(['name' => 'incomes']);

        if (null === $incomeCategory) {
            throw new LogicException('Income category not found.');
        }

        $incomeCategoryId = $incomeCategory->getId();

        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.transactions', 't')
            ->leftJoin('t.transactionCategory', 'tc')
            ->select('w.year, COALESCE(SUM(t.amount), 0) as total')
            ->where('w.year BETWEEN :startYear AND :endYear')
            ->andWhere('tc.id != :incomeCategoryId')
            ->setParameter('startYear', $startYear)
            ->setParameter('endYear', $endYear)
            ->setParameter('incomeCategoryId', $incomeCategoryId)
            ->groupBy('w.year')
            ->orderBy('w.year', 'ASC')
            ->getQuery();

        /** @var array<array{year: int, total: float}> $results */
        $results = $qb->getArrayResult();

        return $results;
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
        $month = MonthEnum::from($month);

        $result = $this->createQueryBuilder('w')
            ->where('w.individual = :user')
            ->andWhere('w.year = :year')
            ->andWhere('w.month = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof Wallet ? $result : null;
    }

    public function findAllWalletByUser($user)
    {
        return $this->createQueryBuilder('w')
            ->where('w.individual = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function userHasWallet(User $user): bool
    {
        $qb = $this->createQueryBuilder('w')
            ->select('1')
            ->where('w.individual = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1);

        return (bool) $qb->getQuery()->getOneOrNullResult();
    }

    public function findSpecificWalletByUser(User $user, int $id): ?Wallet
    {
        $qb = $this->createQueryBuilder('w')
            ->where('w.individual = :user')
            ->andWhere('w.id = :id')
            ->setParameter('user', $user)
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
