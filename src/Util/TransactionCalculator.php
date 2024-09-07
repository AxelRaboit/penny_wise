<?php

namespace App\Util;

use App\Entity\Transaction;

final readonly class TransactionCalculator
{
    private const string INCOMES_CATEGORY = 'incomes';
    private const string EXPENSES_CATEGORY = 'expenses';
    private const string BILLS_CATEGORY = 'bills';
    private const string DEBTS_CATEGORY = 'debts';

    private const string TRANSACTIONS = 'transactions';

    public function calculateTotalSpending(array $transactions): float
    {
        $totalSpending = 0;

        foreach ($this->flattenTransactions($transactions) as $transaction) {
            if ($transaction instanceof Transaction) {
                $category = $transaction->getTransactionCategory()->getName();

                if (in_array($category, [self::EXPENSES_CATEGORY, self::BILLS_CATEGORY, self::DEBTS_CATEGORY], true)) {
                    $totalSpending += $transaction->getAmount();
                }
            }
        }

        return $totalSpending;
    }

    /*public function calculateTotalIncomes(array $transactions): float
    {
        $totalIncomes = 0;

        foreach ($this->flattenTransactions($transactions) as $transaction) {
            if ($transaction instanceof Transaction) {
                if ($transaction->getTransactionCategory()->getName() === self::INCOMES_CATEGORY) {
                    $totalIncomes += $transaction->getAmount();
                }
            }
        }

        return $totalIncomes;
    }*/

    public function calculateTotalIncomes(array $transactions): float
    {
        return array_reduce(
            $this->flattenTransactions($transactions),
            function (float $accumulator, Transaction $transaction): float {
                return $transaction->getTransactionCategory()->getName() === self::INCOMES_CATEGORY
                    ? $accumulator + $transaction->getAmount()
                    : $accumulator;
            },
            0.0
        );
    }

    private function flattenTransactions(array $transactions): array
    {
        $flatTransactions = [];

        if (isset($transactions[0]) && $transactions[0] instanceof Transaction) {
            return $transactions;
        }

        foreach ($transactions as $categoryData) {
            $transactionsFromCategory = $categoryData[self::TRANSACTIONS] ?? [];
            $flatTransactions = array_merge($flatTransactions, $transactionsFromCategory);
        }

        return $flatTransactions;
    }

}
