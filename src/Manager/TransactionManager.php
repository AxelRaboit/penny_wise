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
    public function getAllBudgetInformationByUser(Budget $budget): array
    {
        $expenseTransactions = $this->getExpenseTransactions($budget);
        $billsTransactions = $this->getBillsTransactions($budget);
        $debtTransactions = $this->getDebtTransactions($budget);

        return [
            'expenses' => $expenseTransactions,
            'bills' => $billsTransactions,
            'debt' => $debtTransactions,
        ];
    }

    /**
     * @param Budget $budget
     * @return array<string, TransactionTypeEnum|array<int, Transaction>>
     */
    private function getExpenseTransactions(Budget $budget): array
    {
        $expenseTransactions = $this->transactionRepository
            ->findBy(['budget' => $budget, 'type' => TransactionTypeEnum::EXPENSE()]);

        return [
            'type' => TransactionTypeEnum::EXPENSE(),
            'data' => $expenseTransactions,
        ];
    }

    /**
     * @param Budget $budget
     * @return array<string, TransactionTypeEnum|array<int, Transaction>>
     */
    private function getBillsTransactions(Budget $budget): array
    {
        $billsTransactions = $this->transactionRepository
            ->findBy(['budget' => $budget, 'type' => TransactionTypeEnum::BILLS()]);

        return [
            'type' => TransactionTypeEnum::BILLS(),
            'data' => $billsTransactions,
        ];
    }

    /**
     * @param Budget $budget
     * @return array<string, TransactionTypeEnum|array<int, Transaction>>
     */
    private function getDebtTransactions(Budget $budget): array
    {
        $debtTransactions = $this->transactionRepository
            ->findBy(['budget' => $budget, 'type' => TransactionTypeEnum::DEBT()]);

        return [
            'type' => TransactionTypeEnum::DEBT(),
            'data' => $debtTransactions,
        ];
    }

}