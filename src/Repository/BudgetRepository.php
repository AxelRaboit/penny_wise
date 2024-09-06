<?php

namespace App\Repository;

use App\Dto\MonthDto;
use App\Dto\YearDto;
use App\Entity\Budget;
use App\Enum\MonthEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    private function findUniqueYearsAndMonthsRaw(): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.year, b.month')
            ->groupBy('b.year, b.month')
            ->orderBy('b.year', 'DESC')
            ->addOrderBy('b.month', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    private function transformToYearAndMonthDtos(array $results): array
    {
        $yearsAndMonths = [];

        foreach ($results as $result) {
            $year = (int) $result['year'];
            $month = (int) $result['month'];

            if (!isset($yearsAndMonths[$year])) {
                $yearsAndMonths[$year] = [];
            }

            $monthEnum = MonthEnum::from($month);
            $yearsAndMonths[$year][] = new MonthDto($month, $monthEnum->getName());
        }

        $years = [];
        foreach ($yearsAndMonths as $year => $months) {
            $years[] = new YearDto($year, $months);
        }

        return $years;
    }

    public function findAllBudgets(): array
    {
        $results = $this->findUniqueYearsAndMonthsRaw();
        return $this->transformToYearAndMonthDtos($results);
    }

}
