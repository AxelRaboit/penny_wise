<?php

declare(strict_types=1);

namespace App\Manager;

use App\Dto\TransactionInformationDto;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Enum\TransactionTypeEnum;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

use function Symfony\Component\String\u;

final readonly class TransactionManager
{
    private const string TRANSACTIONS = 'transactions';

    private const string INCOMES_CATEGORY = 'Incomes';

    private const string EXPENSES_CATEGORY = 'Expenses';

    private const string BILLS_CATEGORY = 'Bills';

    private const string DEBTS_CATEGORY = 'Debts';

    public function __construct(
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getAllTransactionInformationByUser(Wallet $wallet): TransactionInformationDto
    {
        $transactions = $this->transactionRepository->findTransactionsByWalletWithRelations($wallet);

        $groupedTransactions = [
            'Incomes' => ['type' => TransactionTypeEnum::INCOMES->getString(), self::TRANSACTIONS => [], 'total' => 0],
            'Bills' => ['type' => TransactionTypeEnum::BILLS->getString(), self::TRANSACTIONS => [], 'total' => 0],
            'Expenses' => ['type' => TransactionTypeEnum::EXPENSES->getString(), self::TRANSACTIONS => [], 'total' => 0],
            'Debts' => ['type' => TransactionTypeEnum::DEBTS->getString(), self::TRANSACTIONS => [], 'total' => 0],
        ];

        foreach ($transactions as $transaction) {
            if ($transaction instanceof Transaction) {
                $transactionCategory = $transaction->getTransactionCategory();
                $category = u($transactionCategory->getName())->lower()->title(true)->toString();

                $budgetInfo = $this->calculateBudgetVsActual($transaction);

                $groupedTransactions[$category][self::TRANSACTIONS][] = [
                    'transaction' => $transaction,
                    'budgetInfo' => $budgetInfo,
                ];

                $groupedTransactions[$category]['total'] += $transaction->getAmount();
            }
        }

        $totalIncomes = $groupedTransactions[self::INCOMES_CATEGORY]['total'];
        $totalBills = $groupedTransactions[self::BILLS_CATEGORY]['total'];
        $totalExpenses = $groupedTransactions[self::EXPENSES_CATEGORY]['total'];
        $totalDebts = $groupedTransactions[self::DEBTS_CATEGORY]['total'];

        $totalSpending = $totalExpenses + $totalBills + $totalDebts;
        $totalIncomesAndStartingBalance = $totalIncomes + $wallet->getStartBalance();
        $totalLeftToSpend = $totalIncomesAndStartingBalance - $totalSpending;

        $totalBudget = $this->calculateTotalBudget($groupedTransactions);
        $leftMinusBudget = $totalLeftToSpend - $totalBudget;

        return new TransactionInformationDto(
            $groupedTransactions,
            $totalIncomesAndStartingBalance,
            $totalIncomes,
            $totalBills,
            $totalExpenses,
            $totalDebts,
            $totalLeftToSpend,
            $totalSpending,
            $leftMinusBudget
        );
    }

    /**
     * Calculate the total budget for transactions.
     *
     * @param array<string, array{type: string, transactions: array<array{transaction: Transaction, budgetInfo: array<string, mixed>}>, total: float}> $transactions
     */
    public function calculateTotalBudget(array $transactions): float
    {
        $totalBudget = 0.0;
        foreach ($transactions as $categoryData) {
            foreach ($categoryData[self::TRANSACTIONS] as $transactionData) {
                /** @var Transaction $transaction */
                $transaction = $transactionData['transaction'];
                if ($transaction->getBudget()) {
                    $totalBudget += (float) $transaction->getBudget();
                }
            }
        }

        return $totalBudget;
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

    public function handleTransactionTags(Transaction $transaction): void
    {
        foreach ($transaction->getTag() as $tag) {
            $transaction->addTag($tag);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    /**
     * @return array{budget: float|null, remaining: float|null, overBudget: bool|null, percentageUsed: float|null}
     */
    public function calculateBudgetVsActual(Transaction $transaction): array
    {
        $budget = $transaction->getBudget();
        $actual = (float) $transaction->getAmount();

        if (null === $budget) {
            return [
                'budget' => null,
                'remaining' => null,
                'overBudget' => null,
                'percentageUsed' => null,
            ];
        }

        $budget = (float) $budget;

        $remaining = $budget - $actual;
        $overBudget = $actual > $budget;
        $percentageUsed = ($actual / $budget) * 100;

        return [
            'budget' => $budget,
            'remaining' => $remaining,
            'overBudget' => $overBudget,
            'percentageUsed' => $percentageUsed,
        ];
    }
}
