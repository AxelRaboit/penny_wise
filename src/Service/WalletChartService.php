<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Transaction\TransactionInformationDto;
use App\Enum\Transaction\TransactionCategoryEnum;
use App\Repository\Transaction\TransactionCategoryRepository;
use App\Repository\Wallet\WalletRepository;
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
        private WalletRepository $walletRepository
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

    public function createTotalSpendingForCurrentAndPreviousNthMonthsChart(int $accountId, int $year, int $month, int $nMonths = 3, string $chartType = Chart::TYPE_BAR): Chart
    {
        $data = $this->monthlyFinancialDataService->getTotalSpendingForCurrentAndPreviousNthMonths($accountId, $year, $month, $nMonths);

        $labels = [];
        $totals = [];

        foreach ($data->getMonthlyTotals() as $totalData) {
            $labels[] = sprintf('%s %d', $totalData['monthName'], $totalData['year']);
            $totals[] = $totalData['total'];
        }

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

    public function createTotalSpendingForCurrentAndAdjacentYearsChart(int $accountId, string $chartType = Chart::TYPE_BAR): Chart
    {
        $currentYear = (int) date('Y');
        $incomeCategory = $this->transactionCategoryRepository->findOneBy(['name' => TransactionCategoryEnum::Incomes->value]);
        if (null === $incomeCategory) {
            throw new LogicException('Income category not found.');
        }

        $incomeCategoryId = (int) $incomeCategory->getId();
        $startYear = $currentYear - 1;
        $endYear = $currentYear + 1;

        $data = $this->walletRepository->fetchTotalSpendingPerYear($startYear, $endYear, $incomeCategoryId, $accountId);

        $labels = [];
        $totals = [];

        foreach ($data as $yearData) {
            $labels[] = (string) $yearData['year'];
            $totals[] = $yearData['total'];
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
