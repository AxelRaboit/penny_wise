<?php

namespace App\Manager;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Enum\TransactionTypeEnum;
use App\Repository\TransactionRepository;
use App\Util\TransactionCalculator;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionManager
{
    private const string EXPENSES_CATEGORY = 'expenses';
    private const string BILLS_CATEGORY = 'bills';
    private const string DEBTS_CATEGORY = 'debts';
    private const string INCOMES_CATEGORY = 'incomes';
    private const string TRANSACTIONS = 'transactions';

    public function __construct(private TransactionRepository $transactionRepository, private TransactionCalculator $transactionCalculator){}

    /**
     * Get all transactions by categories for a given budget
     *
     * @param Budget $budget
     * @return array<string, array{type: string, transactions: Transaction[], total: float}>
     */
    public function getAllTransactionInformationByUser(Budget $budget): array
    {
        $transactions = $this->transactionRepository->findBy(['budget' => $budget]);

        $groupedTransactions = [
            self::EXPENSES_CATEGORY => ['type' => TransactionTypeEnum::EXPENSES()->getString(), self::TRANSACTIONS => [], 'total' => 0],
            self::BILLS_CATEGORY => ['type' => TransactionTypeEnum::BILLS()->getString(), self::TRANSACTIONS => [], 'total' => 0],
            self::DEBTS_CATEGORY => ['type' => TransactionTypeEnum::DEBTS()->getString(), self::TRANSACTIONS => [], 'total' => 0],
            self::INCOMES_CATEGORY => ['type' => TransactionTypeEnum::INCOMES()->getString(), self::TRANSACTIONS => [], 'total' => 0],
        ];

        foreach ($transactions as $transaction) {
            if ($transaction instanceof Transaction) {
                $transactionCategory = $transaction->getTransactionCategory();
                $category = $transactionCategory->getName();

                match ($category) {
                    self::EXPENSES_CATEGORY => $groupedTransactions[self::EXPENSES_CATEGORY][self::TRANSACTIONS][] = $transaction,
                    self::BILLS_CATEGORY => $groupedTransactions[self::BILLS_CATEGORY][self::TRANSACTIONS][] = $transaction,
                    self::DEBTS_CATEGORY => $groupedTransactions[self::DEBTS_CATEGORY][self::TRANSACTIONS][] = $transaction,
                    self::INCOMES_CATEGORY => $groupedTransactions[self::INCOMES_CATEGORY][self::TRANSACTIONS][] = $transaction,
                    default => null,
                };

                match ($category) {
                    self::EXPENSES_CATEGORY => $groupedTransactions[self::EXPENSES_CATEGORY]['total'] += $transaction->getAmount(),
                    self::BILLS_CATEGORY => $groupedTransactions[self::BILLS_CATEGORY]['total'] += $transaction->getAmount(),
                    self::DEBTS_CATEGORY => $groupedTransactions[self::DEBTS_CATEGORY]['total'] += $transaction->getAmount(),
                    self::INCOMES_CATEGORY => $groupedTransactions[self::INCOMES_CATEGORY]['total'] += $transaction->getAmount(),
                    default => null,
                };
            }
        }

        $transactionCategories = [
            self::EXPENSES_CATEGORY => $groupedTransactions[self::EXPENSES_CATEGORY],
            self::BILLS_CATEGORY => $groupedTransactions[self::BILLS_CATEGORY],
            self::DEBTS_CATEGORY => $groupedTransactions[self::DEBTS_CATEGORY],
            self::INCOMES_CATEGORY => $groupedTransactions[self::INCOMES_CATEGORY],
        ];

        $totalIncomes = $groupedTransactions[self::INCOMES_CATEGORY]['total'];
        $totalBills = $groupedTransactions[self::BILLS_CATEGORY]['total'];
        $totalExpenses = $groupedTransactions[self::EXPENSES_CATEGORY]['total'];
        $totalDebts = $groupedTransactions[self::DEBTS_CATEGORY]['total'];

        $totalSpending = $totalExpenses + $totalBills + $totalDebts;
        $totalRemaining = $this->calculateRemainingBalance($budget, $groupedTransactions);

        return [
            'transactionCategories' => $transactionCategories,
            'totalIncomes' => $totalIncomes,
            'totalBills' => $totalBills,
            'totalExpenses' => $totalExpenses,
            'totalDebts' => $totalDebts,
            'totalRemaining' => $totalRemaining,
            'totalSpending' => $totalSpending,
        ];
    }

    /**
     * Calculate the remaining balance for a budget based on transactions
     *
     * @param Budget $budget
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     * @return float
     */
    public function calculateRemainingBalance(Budget $budget, array $transactions): float
    {
        $totalIncomes = $this->calculateTotalIncomes($transactions);
        $totalSpending = $this->calculateTotalSpending($transactions);

        return $budget->getStartBalance() + $totalIncomes - $totalSpending;
    }

    /**
     * Calculate the total incomes from structured transactions array
     *
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     * @return float
     */
    public function calculateTotalIncomes(array $transactions): float
    {
        $flatTransactions = $this->flattenTransactions($transactions);
        return $this->transactionCalculator->calculateTotalIncomes($flatTransactions);
    }

    /**
     * Calculate the total spending from structured transactions array
     *
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     * @return float
     */
    public function calculateTotalSpending(array $transactions): float
    {
        $flatTransactions = $this->flattenTransactions($transactions);
        return $this->transactionCalculator->calculateTotalSpending($flatTransactions);
    }

    /**
     * Flatten the structured transactions array to get only Transaction objects
     *
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     * @return Transaction[]
     */
    private function flattenTransactions(array $transactions): array
    {
        $flatTransactions = [];

        foreach ($transactions as $categoryData) {
            $flatTransactions = array_merge($flatTransactions, $categoryData[self::TRANSACTIONS]);
        }

        return $flatTransactions;
    }
}
