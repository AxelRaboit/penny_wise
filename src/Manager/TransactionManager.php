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
    private const string TRANSACTION_CATEGORIES = 'transactionCategories';

    public function __construct(private TransactionRepository $transactionRepository, private TransactionCalculator $transactionCalculator){}

    /**
     * @param Budget $budget
     * @return array<string, array<string, mixed>> Returns an array of transactions with the type per categories
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
                $category = $transaction->getTransactionCategory()->getName();

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

    public function calculateTotalSpending(array $transactions): float
    {
        return $this->transactionCalculator->calculateTotalSpending($transactions);
    }

    public function calculateTotalIncomes(array $transactions): float
    {
        return $this->transactionCalculator->calculateTotalIncomes($transactions);
    }

    public function calculateRemainingBalance(Budget $budget, array $transactions): float
    {
        return $budget->getStartBalance() + $this->calculateTotalIncomes($transactions) - $this->calculateTotalSpending($transactions);
    }
}