<?php

declare(strict_types=1);

namespace App\Dto\Transaction;

final readonly class TransactionInformationDto
{
    /**
     * @param array<string, mixed> $transactionCategories
     */
    public function __construct(
        private array $transactionCategories,
        private float $totalIncomesAndStartingBalance,
        private float $totalIncomes,
        private float $totalBills,
        private float $totalExpenses,
        private float $totalDebts,
        private float $totalLeftToSpend,
        private float $totalSpending,
        private float $totalBudget,
        private float $totalSaving,
        private float $billsTotalBudget,
        private float $expensesTotalBudget,
        private float $debtsTotalBudget
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getTransactionCategories(): array
    {
        return $this->transactionCategories;
    }

    public function getTotalIncomesAndStartingBalance(): float
    {
        return $this->totalIncomesAndStartingBalance;
    }

    public function getTotalIncomes(): float
    {
        return $this->totalIncomes;
    }

    public function getTotalBills(): float
    {
        return $this->totalBills;
    }

    public function getTotalExpenses(): float
    {
        return $this->totalExpenses;
    }

    public function getTotalDebts(): float
    {
        return $this->totalDebts;
    }

    public function getTotalLeftToSpend(): float
    {
        return $this->totalLeftToSpend;
    }

    public function getTotalSpending(): float
    {
        return $this->totalSpending;
    }

    public function getTotalBudget(): float
    {
        return $this->totalBudget;
    }

    public function getTotalSaving(): float
    {
        return $this->totalSaving;
    }

    public function getBillsTotalBudget(): float
    {
        return $this->billsTotalBudget;
    }

    public function getExpensesTotalBudget(): float
    {
        return $this->expensesTotalBudget;
    }

    public function getDebtsTotalBudget(): float
    {
        return $this->debtsTotalBudget;
    }
}
