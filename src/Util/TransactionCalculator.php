<?php

declare(strict_types=1);

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
            fn (float $accumulator, Transaction $transaction): float => self::INCOMES_CATEGORY === $transaction->getTransactionCategory()->getName()
                ? $accumulator + (float) $transaction->getAmount()
                : $accumulator,
            0.0
        );
    }

    /**
     * Flatten an array of transactions or transaction categories into a single array of Transaction objects.
     *
     * @param array<int, mixed> $transactions an array of transactions or transaction categories
     *
     * @return array<int, Transaction> a flattened array of Transaction objects
     */
    private function flattenTransactions(array $transactions): array
    {
        $flatTransactions = [];

        if ([] !== $transactions && isset($transactions[0]) && $transactions[0] instanceof Transaction) {
            return array_filter($transactions, fn ($transaction): bool => $transaction instanceof Transaction);
        }

        foreach ($transactions as $categoryData) {
            if (is_array($categoryData) && isset($categoryData[self::TRANSACTIONS])) {
                $transactionArray = $categoryData[self::TRANSACTIONS];

                if (is_array($transactionArray)) {
                    foreach ($transactionArray as $transaction) {
                        if ($transaction instanceof Transaction) {
                            $flatTransactions[] = $transaction;
                        }
                    }
                }
            }
        }

        return array_filter($flatTransactions, fn ($transaction): true => $transaction instanceof Transaction);
    }
}
