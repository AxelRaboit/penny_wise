<?php

declare(strict_types=1);

namespace App\Manager;

use App\Dto\TransactionInformationDto;
use App\Entity\Transaction;
use App\Entity\Wallet;
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

    public function __construct(private TransactionRepository $transactionRepository, private TransactionCalculator $transactionCalculator, private EntityManagerInterface $entityManager) {}

    public function getAllTransactionInformationByUser(Wallet $wallet): TransactionInformationDto
    {
        $transactions = $this->transactionRepository->findBy(['wallet' => $wallet]);

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

        $totalIncomes = $groupedTransactions[self::INCOMES_CATEGORY]['total'];
        $totalBills = $groupedTransactions[self::BILLS_CATEGORY]['total'];
        $totalExpenses = $groupedTransactions[self::EXPENSES_CATEGORY]['total'];
        $totalDebts = $groupedTransactions[self::DEBTS_CATEGORY]['total'];

        $totalSpending = $totalExpenses + $totalBills + $totalDebts;
        $totalIncomesAndStartingBalance = $totalIncomes + $wallet->getStartBalance();
        $totalRemaining = $this->calculateRemainingBalance($wallet, $groupedTransactions);

        return new TransactionInformationDto(
            $groupedTransactions,
            $totalIncomesAndStartingBalance,
            $totalIncomes,
            $totalBills,
            $totalExpenses,
            $totalDebts,
            $totalRemaining,
            $totalSpending
        );
    }

    /**
     * Calculate the remaining balance for a wallet based on transactions.
     *
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     */
    public function calculateRemainingBalance(Wallet $wallet, array $transactions): float
    {
        $totalIncomes = $this->calculateTotalIncomes($transactions);
        $totalSpending = $this->calculateTotalSpending($transactions);

        return $wallet->getStartBalance() + $totalIncomes - $totalSpending;
    }

    /**
     * Calculate the total incomes from structured transactions array.
     *
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     */
    public function calculateTotalIncomes(array $transactions): float
    {
        $flatTransactions = $this->flattenTransactions($transactions);

        return $this->transactionCalculator->calculateTotalIncomes($flatTransactions);
    }

    /**
     * Calculate the total spending from structured transactions array.
     *
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     */
    public function calculateTotalSpending(array $transactions): float
    {
        $flatTransactions = $this->flattenTransactions($transactions);

        return $this->transactionCalculator->calculateTotalSpending($flatTransactions);
    }

    /**
     * Flatten the structured transactions array to get only Transaction objects.
     *
     * @param array<string, array{type: string, transactions: Transaction[], total: float}> $transactions
     *
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

    public function findAndDeleteTransactionsByWallet(Wallet $wallet): void
    {
        $transactions = $this->transactionRepository->findTransactionsByWallet($wallet);
        foreach ($transactions as $transaction) {
            $this->entityManager->remove($transaction);
        }

        $this->entityManager->flush();
    }

    public function copyTransactionsFromPreviousMonth(Wallet $currentWallet, float $totalLeftToSpend): void
    {
        $currentWallet->setStartBalance($totalLeftToSpend);

        $this->entityManager->persist($currentWallet);
        $this->entityManager->flush();
    }
}
