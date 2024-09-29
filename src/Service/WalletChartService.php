<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TransactionInformationDto;
use App\Entity\TransactionCategory;
use App\Enum\TransactionCategoryEnum;
use App\Repository\TransactionCategoryRepository;
use LogicException;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final readonly class WalletChartService
{
    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private MonthlyFinancialDataService $monthlyFinancialDataService,
        private TransactionCategoryRepository $transactionCategoryRepository,
    ) {}

    /**
     * Creates a chart visualizing the total spending and the amount left to spend.
     *
     * @param TransactionInformationDto $transactions Data transfer object containing transaction information
     * @param string                    $chartType    Chart type to be generated (e.g., 'doughnut', 'bar', etc.)
     *
     * @return Chart A Chart object configured to display the spending distribution
     */
    public function createLeftToSpendChart(TransactionInformationDto $transactions, string $chartType = Chart::TYPE_DOUGHNUT): Chart
    {
        $chart = $this->chartBuilder->createChart($chartType);

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
     * Creates a chart representing the total spending for the current and previous N months.
     *
     * @param int    $year      The year for which the chart is being generated
     * @param int    $month     The month for which the chart is being generated
     * @param int    $nMonths   The number of previous months to include in the chart
     * @param string $chartType The type of chart to create (e.g., 'bar', 'line')
     *
     * @return Chart A Chart object configured to display the total spending distribution over the specified months
     */
    public function createTotalSpendingForCurrentAndPreviousNthMonthsChart(int $year, int $month, int $nMonths = 3, string $chartType = Chart::TYPE_BAR): Chart
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

        $chart = $this->chartBuilder->createChart($chartType);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total spent',
                    'backgroundColor' => array_fill(0, count($labels), 'RGB(24, 24, 27)'),
                    'borderColor' => array_fill(0, count($labels), 'RGB(24, 24, 27)'),
                    'data' => $totals,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Creates a line chart representing the total spending for the current, previous, and next years.
     *
     * @param string $chartType The type of chart to create (e.g., 'line')
     *
     * @return Chart The generated line chart containing total spending data
     *
     * @throws LogicException if the income category is not found
     */
    public function createTotalSpendingForCurrentAndAdjacentYearsChart(string $chartType = Chart::TYPE_BAR): Chart
    {
        $currentYear = (int) date('Y');
        $previousYear = $currentYear - 1;
        $nextYear = $currentYear + 1;

        /** @var TransactionCategory|null $incomeCategory */
        $incomeCategory = $this->transactionCategoryRepository->findOneBy(['name' => TransactionCategoryEnum::Incomes->value]);
        if (null === $incomeCategory) {
            throw new LogicException('Income category not found.');
        }

        $incomeCategoryId = $incomeCategory->getId();
        if (!is_int($incomeCategoryId)) {
            throw new LogicException('Income category ID must be an integer.');
        }

        /** @var array<array{year: int, total: float}> $data */
        $data = $this->monthlyFinancialDataService->getTotalSpendingPerYear($previousYear, $nextYear, $incomeCategoryId);

        /** @var string[] $labels */
        $labels = [];
        /** @var float[] $totals */
        $totals = [];

        foreach ($data as $totalData) {
            $labels[] = (string) $totalData['year'];
            $totals[] = (float) $totalData['total'];
        }

        $chart = $this->chartBuilder->createChart($chartType);

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
}
