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

    /**
     * @param array<Transaction> $transactions
     */
    public function calculateTotalSpending(array $transactions): float
    {
        $totalSpending = 0.0;

        foreach ($this->flattenTransactions($transactions) as $transaction) {
            if ($transaction instanceof Transaction) {
                $category = $transaction->getTransactionCategory()->getName();

                if (in_array($category, [self::EXPENSES_CATEGORY, self::BILLS_CATEGORY, self::DEBTS_CATEGORY], true)) {
                    $totalSpending += (float) $transaction->getAmount();
                }
            }
        }

        return $totalSpending;
    }

    /**
     * @param array<Transaction> $transactions
     */
    public function calculateTotalIncomes(array $transactions): float
    {
        return array_reduce(
            $this->flattenTransactions($transactions),
            fn(float $accumulator, Transaction $transaction): float => $transaction->getTransactionCategory()->getName() === self::INCOMES_CATEGORY
                ? $accumulator + (float) $transaction->getAmount()
                : $accumulator,
            0.0
        );
    }

    /**
     * @param array<int, Transaction|array{type: string, transactions: array<Transaction>, total: float}> $transactions
     * @return array<Transaction>
     */
    private function flattenTransactions(array $transactions): array
    {
        $flatTransactions = [];

        if ($transactions !== [] && isset($transactions[0]) && $transactions[0] instanceof Transaction) {
            /** @var array<int, Transaction> $transactions */
            return $transactions;
        }

        foreach ($transactions as $categoryData) {
            if (is_array($categoryData)) {
                /** @var array<Transaction> $transactionArray */
                $transactionArray = $categoryData[self::TRANSACTIONS];
                $flatTransactions = array_merge($flatTransactions, $transactionArray);
            }
        }

        return $flatTransactions;
    }
}

