<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\MonthDto;
use App\Dto\TotalSpendingFromNMonthsDto;
use App\Dto\YearDto;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Exception\WalletNotFoundWithinLimitException;
use App\Util\WalletHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    private const string INCOME_CATEGORY_ID = '4';

    public function __construct(ManagerRegistry $registry, private readonly WalletHelper $walletHelper)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * @return array<array{year: int, month: int}>
     */
    private function findUniqueYearsAndMonthsRaw(): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.year, b.month')
            ->groupBy('b.year, b.month')
            ->orderBy('b.year', Order::Descending->value)
            ->addOrderBy('b.month', Order::Ascending->value);

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
     * @param array<array{year: int, month: int}> $results
     *
     * @return array<YearDto>
     */
    private function transformToYearAndMonthDtos(array $results): array
    {
        $yearsAndMonths = [];

        foreach ($results as $result) {
            $year = $result['year'];
            $month = $result['month'];

            $monthEnum = MonthEnum::from($month);
            $yearsAndMonths[$year][] = new MonthDto($month, $monthEnum->getName());
        }

        return array_map(static fn (int $year, array $months): YearDto => new YearDto($year, $months), array_keys($yearsAndMonths), $yearsAndMonths);
    }

    /**
     * @return array<YearDto>
     */
    public function findAllWallets(): array
    {
        $results = $this->findUniqueYearsAndMonthsRaw();

        return $this->transformToYearAndMonthDtos($results);
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
        $qb = $this->createQueryBuilder('b')
            ->select('b.month')
            ->where('b.year = :year')
            ->setParameter('year', $year)
            ->orderBy('b.month', Order::Ascending->value)
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
     * Retrieves the total spending for the current and previous n months.
     */
    public function getTotalSpendingForCurrentAndPreviousNthMonths(int $year, int $month, int $nMonths): TotalSpendingFromNMonthsDto
    {
        $totals = [];

        for ($i = 0; $i < $nMonths; ++$i) {
            $monthEnum = MonthEnum::from($month);

            $totals[] = [
                'year' => $year,
                'monthNumber' => $month,
                'monthName' => $monthEnum->getName(),
                'total' => $this->getTotalSpendingByMonth($year, $month),
            ];

            $previousMonth = $this->walletHelper->getPreviousMonthAndYear($year, $month);
            $year = $previousMonth['year'];
            $month = $previousMonth['month'];
        }

        return new TotalSpendingFromNMonthsDto($totals);
    }

    /**
     * Retrieves the total spending for a given year and month, excluding a specific income category.
     *
     * @param int $year  the year for which the total spending is calculated
     * @param int $month the month for which the total spending is calculated
     *
     * @return float the total spending amount for the specified year and month
     */
    private function getTotalSpendingByMonth(int $year, int $month): float
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.transactions', 't')
            ->leftJoin('t.transactionCategory', 'tc')
            ->select('COALESCE(SUM(t.amount), 0) as totalSpending')
            ->where('b.year = :year')
            ->andWhere('b.month = :month')
            ->andWhere('tc.id != :incomeCategoryId')
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->setParameter('incomeCategoryId', self::INCOME_CATEGORY_ID)
            ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

    /**
     * Retrieves the total spending per year, excluding the income category.
     *
     * @param int $startYear the starting year of the range
     * @param int $endYear   the ending year of the range
     *
     * @return array<array{year: int, total: float}> the total spending for each year
     */
    public function getTotalSpendingPerYear(int $startYear, int $endYear): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.transactions', 't')
            ->leftJoin('t.transactionCategory', 'tc')
            ->select('b.year, COALESCE(SUM(t.amount), 0) as total')
            ->where('b.year BETWEEN :startYear AND :endYear')
            ->andWhere('tc.id != :incomeCategoryId')
            ->setParameter('startYear', $startYear)
            ->setParameter('endYear', $endYear)
            ->setParameter('incomeCategoryId', self::INCOME_CATEGORY_ID)
            ->groupBy('b.year')
            ->orderBy('b.year', 'ASC')
            ->getQuery();

        /** @var array<array{year: int, total: string}> $results */
        $results = $qb->getArrayResult();

        return array_map(static fn (array $result): array => [
            'year' => $result['year'],
            'total' => (float) $result['total'],
        ], $results);
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
    public function findWalletFromUser(User $user, int $year, int $month): ?Wallet
    {
        $month = MonthEnum::from($month);

        $result = $this->createQueryBuilder('b')
            ->where('b.individual = :user')
            ->andWhere('b.year = :year')
            ->andWhere('b.month = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof Wallet ? $result : null;
    }

    public function userHasWallet(User $user): bool
    {
        $qb = $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.individual = :user')
            ->setParameter('user', $user);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Finds the previous wallet for a given user based on the specified year and month.
     * The method searches backward month-by-month until a wallet is found or the limit of months is reached.
     *
     * @param User $user          the user for whom the previous wallet is being searched
     * @param int  $year          the year from which the search starts
     * @param int  $month         the month from which the search starts
     * @param int  $maxMonthsBack the maximum number of months to search backward
     *
     * @return Wallet|null the found wallet or null if no previous wallet is found within the limit or if the limit is exceeded
     *
     * @throws WalletNotFoundWithinLimitException
     */
    public function findPreviousWallet(User $user, int $year, int $month, int $maxMonthsBack = 12): ?Wallet
    {
        --$month;
        $monthsSearched = 0;

        while ($year > 0 && $monthsSearched < $maxMonthsBack) {
            if (0 === $month) {
                $month = 12;
                --$year;
            }

            $wallet = $this->findWalletFromUser($user, $year, $month);
            if ($wallet instanceof Wallet) {
                return $wallet;
            }

            --$month;
            ++$monthsSearched;
        }

        if ($monthsSearched >= $maxMonthsBack) {
            throw new WalletNotFoundWithinLimitException($maxMonthsBack);
        }

        return null;
    }
}
