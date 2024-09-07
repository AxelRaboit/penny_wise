<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\BudgetRepository;
use Exception;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final readonly class BudgetService
{
    public function __construct(
        private BudgetRepository      $budgetRepository,
        private ChartBuilderInterface $chartBuilder,
    ) {}

    /**
     * @throws Exception
     */
    public function getBudgetByUser(User $user, int $year, int $month): Budget
    {
        $budget = $this->budgetRepository
            ->findOneBy(['individual' => $user, 'year' => $year, 'month' => $month]);

        if (!$budget) {
            $message = `No budget found for user ${user}, year ${year} and month ${month}`;
            throw new Exception($message);
        }

        return $budget;
    }

    /**
     * @param array<string, array<string, array<Transaction>>> $transactions
     * @return Chart
     */
    public function createBudgetChart(array $transactions): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => ['Total Spending', 'Remaining Balance'],
            'datasets' => [
                [
                    'backgroundColor' => [
                        'rgb(201, 203, 207)', // Total spending
                        'rgb(101, 163, 13)' // Remaining balance
                    ],
                    'borderColor' => [
                        'rgb(201, 203, 207)',
                        'rgb(101, 163, 13)'
                    ],
                    'data' => [
                        $transactions['totalSpending'],
                        $transactions['totalRemaining']
                    ],
                ],
            ],
        ]);

        return $chart;
    }
}