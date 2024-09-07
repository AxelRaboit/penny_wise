<?php

namespace App\Util;

use App\Entity\Budget;
use App\Entity\Transaction;

final readonly class BudgetCalculator
{

    public function __construct(private TransactionCalculator $transactionCalculator){}

    /**
     * @param Budget $budget
     * @param array<Transaction> $transactions
     * @return float
     */
    public function calculateRemainingBalance(Budget $budget, array $transactions): float
    {
        dump($transactions);
        $totalIncomes = $this->transactionCalculator->calculateTotalIncomes($transactions);
        $totalSpending = $this->transactionCalculator->calculateTotalSpending($transactions);

        return $budget->getStartBalance() + $totalIncomes - $totalSpending;
    }
}