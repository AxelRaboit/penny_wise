<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TransactionInformationDto;
use App\Entity\User;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final readonly class WalletChartService
{
    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private MonthlyFinancialDataService $monthlyFinancialDataService,
    ) {}

    /**
     * Creates a doughnut chart visualizing the total spending and the amount left to spend.
     *
     * @param TransactionInformationDto $transactions data transfer object containing transaction information
     *
     * @return Chart a Chart object configured to display the spending distribution
     */
    public function createLeftToSpendChart(TransactionInformationDto $transactions): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $totalSpending = $transactions->getTotalLeftToSpend() > 0 ? $transactions->getTotalLeftToSpend() : $transactions->getTotalSpending();
        $isDataPresent = $totalSpending > self::DEFAULT_BALANCE;

        $chart->setData([
            'labels' => ['Total spent', 'Left to spend'],
            'isDataPresent' => $isDataPresent,
            'datasets' => [
                [
                    'backgroundColor' => [
                        'rgb(201, 203, 207)',
                        'RGB(24, 24, 27)',
                    ],
                    'borderColor' => [
                        'rgb(201, 203, 207)',
                        'RGB(24, 24, 27)',
                    ],
                    'data' => [
                        $transactions->getTotalSpending(),
                        $transactions->getTotalLeftToSpend(),
                    ],
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Creates a bar chart representing the total spending for the current and previous N months.
     *
     * @param int $year    the year for which the chart is being generated
     * @param int $month   the month for which the chart is being generated
     * @param int $nMonths the number of previous months to include in the chart
     *
     * @return Chart a Chart object configured to display the total spending distribution over the specified months
     */
    public function createTotalSpendingForCurrentAndPreviousNthMonthsChart(int $year, int $month, int $nMonths = 3): Chart
    {
        $data = $this->monthlyFinancialDataService->getTotalSpendingForCurrentAndPreviousNthMonths($year, $month, $nMonths);

        $labels = [];
        $totals = [];

        foreach ($data->getMonthlyTotals() as $totalData) {
            $labels[] = sprintf('%s %d', $totalData['monthName'], $totalData['year']);
            $totals[] = $totalData['total'];
        }

        $labels = array_reverse($labels);
        $totals = array_reverse($totals);

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total spent',
                    'backgroundColor' => array_fill(0, $nMonths, 'RGB(24, 24, 27)'),
                    'borderColor' => array_fill(0, $nMonths, 'RGB(24, 24, 27)'),
                    'data' => $totals,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Creates a bar chart representing the total spending for the current,
     * previous, and next years.
     *
     * @return Chart the generated bar chart containing total spending data
     */
    public function createTotalSpendingForCurrentAndAdjacentYearsChart(): Chart
    {
        $currentYear = (int) date('Y');

        $previousYear = $currentYear - 1;
        $nextYear = $currentYear + 1;

        $data = $this->monthlyFinancialDataService->getTotalSpendingPerYear($previousYear, $nextYear);

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
                    'label' => 'Total spent per year',
                    'backgroundColor' => array_fill(0, count($labels), 'RGB(24, 24, 27)'),
                    'borderColor' => array_fill(0, count($labels), 'RGB(24, 24, 27)'),
                    'data' => $totals,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Creates a bar chart representing the total savings for the current month
     * and the specified number of previous months for a given user.
     *
     * @param User $user    the user for whom the savings data should be fetched
     * @param int  $year    the year for the current month
     * @param int  $month   the index of the current month (1-12)
     * @param int  $nMonths the number of previous months to include in the chart (defaults to 4)
     *
     * @return Chart the generated bar chart containing total savings data
     */
    public function createTotalSavingForCurrentAndPreviousMonthsChart(User $user, int $year, int $month, int $nMonths = 3): Chart
    {
        $data = $this->monthlyFinancialDataService->getTotalSavingForCurrentAndPreviousNthMonths($user, $year, $month, $nMonths);

        $labels = [];
        $savings = [];

        foreach ($data->getMonthlyTotals() as $totalData) {
            $labels[] = sprintf('%s %d', $totalData['monthName'], $totalData['year']);
            $savings[] = $totalData['totalSaving'];
        }

        $labels = array_reverse($labels);
        $savings = array_reverse($savings);

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Savings',
                    'backgroundColor' => array_fill(0, count($labels), 'RGB(24, 24, 27)'),
                    'borderColor' => array_fill(0, count($labels), 'RGB(24, 24, 27)'),
                    'data' => $savings,
                ],
            ],
        ]);

        return $chart;
    }
}
