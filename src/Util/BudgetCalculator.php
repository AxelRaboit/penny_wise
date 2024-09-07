<?php

namespace App\Util;

use App\Entity\Budget;

final readonly class BudgetCalculator
{

    public function __construct(private TransactionCalculator $transactionCalculator){}

    public function calculateRemainingBalance(Budget $budget, array $transactions): float
    {
        $totalIncomes = $this->transactionCalculator->calculateTotalIncomes($transactions);
        $totalSpending = $this->transactionCalculator->calculateTotalSpending($transactions);

        return $budget->getStartBalance() + $totalIncomes - $totalSpending;
    }
}
