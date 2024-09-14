<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Enum\MonthEnum;
use App\Repository\BudgetRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final readonly class BudgetService
{
    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private BudgetRepository $budgetRepository,
        private ChartBuilderInterface $chartBuilder,
    ) {}

    public function getBudgetByUser(User $user, int $year, int $month): ?Budget
    {
        /** @var Budget|null $budget */
        $budget = $this->budgetRepository
            ->findMonthlyBudgetFromUser($user, $year, MonthEnum::from($month));

        return $budget;
    }

    /**
     * @param array<string, array<string, array<Transaction>>> $transactions
     */
    public function createLeftToSpendChart(array $transactions): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        /** @var float $totalSpending */
        $totalSpending = $transactions['totalSpending'] ?? self::DEFAULT_BALANCE;
        $isDataPresent = $totalSpending > self::DEFAULT_BALANCE;

        $chart->setData([
            'labels' => ['Total Spending', 'Remaining Balance'],
            'isDataPresent' => $isDataPresent,
            'datasets' => [
                [
                    'backgroundColor' => [
                        'rgb(201, 203, 207)',
                        'rgb(30, 41, 59)',
                    ],
                    'borderColor' => [
                        'rgb(201, 203, 207)',
                        'rgb(30, 41, 59)',
                    ],
                    'data' => [
                        $transactions['totalSpending'],
                        $transactions['totalRemaining'],
                    ],
                ],
            ],
        ]);

        return $chart;
    }

    public function createTotalSpendingForCurrentAndPreviousNthMonthsChart(int $year, int $month, int $nMonths): Chart
    {
        $data = $this->budgetRepository->getTotalSpendingForCurrentAndPreviousNthMonths($year, $month, $nMonths);

        $labels = [];
        $totals = [];

        foreach ($data->getMonthlyTotals() as $totalData) {
            $labels[] = $totalData['monthName'].' '.$totalData['year'];
            $totals[] = $totalData['total'];
        }

        $labels = array_reverse($labels);
        $totals = array_reverse($totals);

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Spending',
                    'backgroundColor' => array_fill(0, $nMonths, 'rgb(30, 41, 59)'),
                    'borderColor' => array_fill(0, $nMonths, 'rgb(30, 41, 59)'),
                    'data' => $totals,
                ],
            ],
        ]);

        return $chart;
    }

    public function createTotalSpendingForCurrentAndAdjacentYearsChart(): Chart
    {
        $currentYear = (int) date('Y');

        $previousYear = $currentYear - 1;
        $nextYear = $currentYear + 1;

        $data = $this->budgetRepository->getTotalSpendingPerYear($previousYear, $nextYear);

        $labels = [];
        $totals = [];

        foreach ($data as $totalData) {
            $labels[] = $totalData['year'];
            $totals[] = $totalData['total'];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Spending per Year',
                    'backgroundColor' => array_fill(0, count($labels), 'rgb(30, 41, 59)'),
                    'borderColor' => array_fill(0, count($labels), 'rgb(30, 41, 59)'),
                    'data' => $totals,
                ],
            ],
        ]);

        return $chart;
    }
}
