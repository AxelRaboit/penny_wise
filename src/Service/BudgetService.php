<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\BudgetRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final readonly class BudgetService
{
    public function __construct(private BudgetRepository $budgetRepository, private ChartBuilderInterface $chartBuilder){}

    public function getBudgetByUser(User $user, int $year, int $month): Budget
    {
        $budget = $this->budgetRepository
            ->findOneBy(['individual' => $user, 'year' => $year, 'month' => $month]);

        if (!$budget) {
            $message = `No budget found for user ${user}, year ${year} and month ${month}`;
            throw new \RuntimeException($message);
        }

        return $budget;
    }

    /**
     * @param Budget $budget
     * @param array<string, array<string, array<Transaction>>> $transactions
     * @return float
     */
    public function calculateTotalIncomes(Budget $budget, array $transactions): float
    {
        $totalIncomes = $budget->getStartBalance();
        foreach ($transactions['incomes']['data'] as $transaction) {
            $totalIncomes += $transaction->getAmount();
        }
        return $totalIncomes;
    }

    /**
     * @param array<string, array<string, array<Transaction>>> $transactions
     * @return float
     */
    public function calculateTotalSpending(array $transactions): float
    {
        $totalSpending = 0;

        $allSpendingTransactions = array_merge(
            $transactions['expenses']['data'],
            $transactions['bills']['data'],
            $transactions['debts']['data']
        );

        foreach ($allSpendingTransactions as $transaction) {
            $totalSpending += $transaction->getAmount();
        }

        return $totalSpending;
    }

    /**
     * @param Budget $budget
     * @param array<string, array<string, array<Transaction>>> $transactions
     * @return float
     */
    public function getRemainingBalance(Budget $budget, array $transactions): float
    {
        $totalIncomes = $this->calculateTotalIncomes($budget, $transactions);
        $totalSpending = $this->calculateTotalSpending($transactions);

        return $totalIncomes - $totalSpending;
    }

    /**
     * @param Budget $budget
     * @param array<string, array<string, array<Transaction>>> $transactions
     * @return Chart
     */
    public function createBudgetChart(Budget $budget, array $transactions): Chart
    {
        $totalSpending = $this->calculateTotalSpending($transactions);
        $remainingBalance = $this->getRemainingBalance($budget, $transactions);

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
                    'data' => [$totalSpending, $remainingBalance],
                ],
            ],
        ]);

        return $chart;
    }
}