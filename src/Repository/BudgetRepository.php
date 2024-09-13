<?php

namespace App\Repository;

use App\Dto\MonthDto;
use App\Dto\TotalSpendingFromNMonthsDto;
use App\Dto\YearDto;
use App\Entity\Budget;
use App\Enum\MonthEnum;
use App\Util\BudgetHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    private const string INCOME_CATEGORY_ID = '4';

    public function __construct(ManagerRegistry $registry, private readonly BudgetHelper $budgetHelper)
    {
        parent::__construct($registry, Budget::class);
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

        return array_map(static fn(int $year, array $months): YearDto => new YearDto($year, $months), array_keys($yearsAndMonths), $yearsAndMonths);
    }

    /**
     * @return array<YearDto>
     */
    public function findAllBudgets(): array
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
    public function getYearWithMonths(int $year): YearDto
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

        for ($i = 0; $i < $nMonths; $i++) {
            $monthEnum = MonthEnum::from($month);

            $totals[] = [
                'year' => $year,
                'monthNumber' => $month,
                'monthName' => $monthEnum->getName(),
                'total' => $this->getTotalSpendingByMonth($year, $month),
            ];

            $previousMonth = $this->budgetHelper->getPreviousMonthAndYear($year, $month);
            $year = $previousMonth['year'];
            $month = $previousMonth['month'];
        }

        return new TotalSpendingFromNMonthsDto($totals);
    }

    /**
     * Retrieves the total spending for a given year and month, excluding a specific income category.
     *
     * @param int $year The year for which the total spending is calculated.
     * @param int $month The month for which the total spending is calculated.
     * @return float     The total spending amount for the specified year and month.
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
     * @param int $startYear The starting year of the range.
     * @param int $endYear The ending year of the range.
     * @return array<array{year: int, total: float}> The total spending for each year.
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

        return array_map(static fn(array $result): array => [
            'year' => (int) $result['year'],
            'total' => (float) $result['total'],
        ], $results);
    }
}
