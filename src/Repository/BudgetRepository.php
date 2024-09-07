<?php

namespace App\Repository;

use App\Dto\MonthDto;
use App\Dto\YearDto;
use App\Entity\Budget;
use App\Enum\MonthEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

        return array_map(static function (int $year, array $months): YearDto {
            return new YearDto($year, $months);
        }, array_keys($yearsAndMonths), $yearsAndMonths);
    }

    /**
     * @return array<YearDto>
     */
    public function findAllBudgets(): array
    {
        $results = $this->findUniqueYearsAndMonthsRaw();
        return $this->transformToYearAndMonthDtos($results);
    }

    public function getAnnualBudget(int $year): YearDto
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.month')
            ->where('b.year = :year')
            ->setParameter('year', $year)
            ->orderBy('b.month', Order::Ascending->value)
            ->getQuery();

        $months = $qb->getArrayResult();

        $result = [];
        foreach ($months as $month) {
            $monthNumber = (int) $month['month'];
            $monthEnum = MonthEnum::from($monthNumber);
            $result[] = new MonthDto($monthEnum->value, $monthEnum->getName());
        }

        return new YearDto($year, $result);
    }
}

