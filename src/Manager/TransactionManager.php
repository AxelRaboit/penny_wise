<?php

namespace App\Manager;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Enum\TransactionTypeEnum;
use App\Repository\TransactionRepository;

final readonly class TransactionManager
{
    public function __construct(private TransactionRepository $transactionRepository){}

    /**
     * @param Budget $budget
     * @return array<array<string, mixed>>
     */
    public function getAllTransactionInformationByUser(Budget $budget): array
    {
        $expenseTransactions = $this->getExpenseTransactions($budget);
        $billsTransactions = $this->getBillsTransactions($budget);
        $debtTransactions = $this->getDebtTransactions($budget);
        $incomeTransactions = $this->getIncomeTransactions($budget);

        return [
            'expenses' => $expenseTransactions,
            'bills' => $billsTransactions,
            'debts' => $debtTransactions,
            'incomes' => $incomeTransactions,
        ];
    }

    /**
     * @param Budget $budget
     * @return array<string, TransactionTypeEnum|array<int, Transaction>>
     */
    private function getExpenseTransactions(Budget $budget): array
    {
        $expenseTransactions = $this->transactionRepository->findByBudgetAndCategory($budget, TransactionTypeEnum::EXPENSES()->getString());

        $totalExpenses = $this->getTotalExpenses($budget);

        return [
            'type' => TransactionTypeEnum::EXPENSES()->getString(),
            'data' => $expenseTransactions,
            'total' => $totalExpenses,
        ];
    }

    /**
     * @param Budget $budget
     * @return array<string, TransactionTypeEnum|array<int, Transaction>>
     */
    private function getBillsTransactions(Budget $budget): array
    {
        $billsTransactions = $this->transactionRepository->findByBudgetAndCategory($budget, TransactionTypeEnum::BILLS()->getString());

        $totalBills = $this->getTotalBills($budget);

        return [
            'type' => TransactionTypeEnum::BILLS()->getString(),
            'data' => $billsTransactions,
            'total' => $totalBills,
        ];
    }

    /**
     * @param Budget $budget
     * @return array<string, TransactionTypeEnum|array<int, Transaction>>
     */
    private function getDebtTransactions(Budget $budget): array
    {
        $debtTransactions = $this->transactionRepository->findByBudgetAndCategory($budget, TransactionTypeEnum::DEBTS()->getString());

        $totalDebts = $this->getTotalDebts($budget);

        return [
            'type' => TransactionTypeEnum::DEBTS()->getString(),
            'data' => $debtTransactions,
            'total' => $totalDebts,
        ];
    }

    /**
     * @param Budget $budget
     * @return array<string, TransactionTypeEnum|array<int, Transaction>>
     */
    private function getIncomeTransactions(Budget $budget): array
    {
        $incomeTransactions = $this->transactionRepository->findByBudgetAndCategory($budget, TransactionTypeEnum::INCOMES()->getString());

        $totalIncomes = $this->getTotalIncomes($budget);

        return [
            'type' => TransactionTypeEnum::INCOMES()->getString(),
            'data' => $incomeTransactions,
            'total' => $totalIncomes,
        ];
    }

    private function getTotalIncomes(Budget $budget): float
    {
        $totalIncomes = 0;
        $incomeCategory = TransactionTypeEnum::INCOMES()->getString();

        $incomes = $this->transactionRepository->findByBudgetAndCategory($budget, $incomeCategory);

        foreach ($incomes as $income) {
            $totalIncomes += $income->getAmount();
        }

        return $totalIncomes;
    }

    private function getTotalExpenses(Budget $budget): float
    {
        $totalExpenses = 0;
        $expenseCategory = TransactionTypeEnum::EXPENSES()->getString();

        $expenses = $this->transactionRepository->findByBudgetAndCategory($budget, $expenseCategory);

        foreach ($expenses as $transaction) {
            $totalExpenses += $transaction->getAmount();
        }

        return $totalExpenses;
    }

    private function getTotalBills(Budget $budget): float
    {
        $totalBills = 0;
        $billCategory = TransactionTypeEnum::BILLS()->getString();

        $bills = $this->transactionRepository->findByBudgetAndCategory($budget, $billCategory);

        foreach ($bills as $transaction) {
            $totalBills += $transaction->getAmount();
        }

        return $totalBills;
    }

    private function getTotalDebts(Budget $budget): float
    {
        $totalDebts = 0;
        $debtCategory = TransactionTypeEnum::DEBTS()->getString();

        $debts = $this->transactionRepository->findByBudgetAndCategory($budget, $debtCategory);

        foreach ($debts as $transaction) {
            $totalDebts += $transaction->getAmount();
        }

        return $totalDebts;
    }
}