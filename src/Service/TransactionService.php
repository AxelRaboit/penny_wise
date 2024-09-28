<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TransactionInformationDto;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Enum\TransactionCategoryEnum;
use App\Exception\NoPreviousTransactionsException;
use App\Exception\NoPreviousWalletException;
use App\Exception\WalletNotFoundWithinLimitException;
use App\Manager\TransactionManager;
use App\Repository\TransactionCategoryRepository;
use App\Repository\TransactionRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final readonly class TransactionService
{
    private const string TRANSACTIONS = 'transactions';

    private const float DEFAULT_BALANCE = 0.0;

    public function __construct(
        private TransactionManager $transactionManager,
        private EntityManagerInterface $entityManager,
        private TransactionRepository $transactionRepository,
        private WalletService $walletService,
        private TransactionCategoryRepository $transactionCategoryRepository
    ) {}

    /**
     * @return TransactionInformationDto Returns a data transfer object with transaction information by user for a given wallet
     */
    public function getAllTransactionInformationByUser(Wallet $wallet): TransactionInformationDto
    {
        $transactions = $this->transactionRepository->findTransactionsByWalletWithRelations($wallet);

        $groupedTransactions = [
            TransactionCategoryEnum::Incomes->value => ['type' => TransactionCategoryEnum::Incomes->value, self::TRANSACTIONS => [], 'total' => self::DEFAULT_BALANCE, 'totalBudget' => self::DEFAULT_BALANCE],
            TransactionCategoryEnum::Bills->value => ['type' => TransactionCategoryEnum::Bills->value, self::TRANSACTIONS => [], 'total' => self::DEFAULT_BALANCE, 'totalBudget' => self::DEFAULT_BALANCE],
            TransactionCategoryEnum::Expenses->value => ['type' => TransactionCategoryEnum::Expenses->value, self::TRANSACTIONS => [], 'total' => self::DEFAULT_BALANCE, 'totalBudget' => self::DEFAULT_BALANCE],
            TransactionCategoryEnum::Debts->value => ['type' => TransactionCategoryEnum::Debts->value, self::TRANSACTIONS => [], 'total' => self::DEFAULT_BALANCE, 'totalBudget' => self::DEFAULT_BALANCE],
        ];

        foreach ($transactions as $transaction) {
            if ($transaction instanceof Transaction) {
                $transactionCategory = $transaction->getTransactionCategory();
                $category = $transactionCategory->getName();

                $budgetInfo = $this->calculateBudgetVsActual($transaction);

                $groupedTransactions[$category][self::TRANSACTIONS][] = [
                    'transaction' => $transaction,
                    'budgetInfo' => $budgetInfo,
                ];

                $groupedTransactions[$category]['total'] += $transaction->getAmount();

                if (null !== $transaction->getBudget()) {
                    $groupedTransactions[$category]['totalBudget'] += (float) $transaction->getBudget();
                }
            }
        }

        $totalIncomes = $groupedTransactions[TransactionCategoryEnum::Incomes->value]['total'];
        $totalBills = $groupedTransactions[TransactionCategoryEnum::Bills->value]['total'];
        $totalExpenses = $groupedTransactions[TransactionCategoryEnum::Expenses->value]['total'];
        $totalDebts = $groupedTransactions[TransactionCategoryEnum::Debts->value]['total'];

        $totalSpending = $totalExpenses + $totalBills + $totalDebts;
        $totalIncomesAndStartingBalance = $totalIncomes + $wallet->getStartBalance();
        $totalLeftToSpend = $totalIncomesAndStartingBalance - $totalSpending;

        $budgets = $this->calculateTotalBudget($groupedTransactions);
        $totalBudget = $totalIncomesAndStartingBalance - $budgets;
        $totalSaving = $totalLeftToSpend - $totalBudget;

        return new TransactionInformationDto(
            $groupedTransactions,
            $totalIncomesAndStartingBalance,
            $totalIncomes,
            $totalBills,
            $totalExpenses,
            $totalDebts,
            $totalLeftToSpend,
            $totalSpending,
            $totalBudget,
            $totalSaving,
            $groupedTransactions[TransactionCategoryEnum::Bills->value]['totalBudget'],
            $groupedTransactions[TransactionCategoryEnum::Expenses->value]['totalBudget'],
            $groupedTransactions[TransactionCategoryEnum::Debts->value]['totalBudget']
        );
    }

    /**
     * Calculate the total budget for transactions.
     *
     * @param array<string, array{type: string, transactions: array<array{transaction: Transaction, budgetInfo: array<string, mixed>}>, total: float}> $transactions
     */
    private function calculateTotalBudget(array $transactions): float
    {
        $totalBudget = self::DEFAULT_BALANCE;
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

    /**
     * @return array{budget: float|null, remaining: float|null, overBudget: bool|null, percentageUsed: float|null}
     */
    public function calculateBudgetVsActual(Transaction $transaction): array
    {
        /** @var float|null $budget */
        $budget = $transaction->getBudget();
        if (null === $budget || self::DEFAULT_BALANCE === (float) $budget) {
            return [
                'budget' => null,
                'remaining' => null,
                'overBudget' => null,
                'percentageUsed' => null,
            ];
        }

        $actual = (float) $transaction->getAmount();
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

    /**
     * @throws WalletNotFoundWithinLimitException
     * @throws DateMalformedStringException
     */
    public function copyTransactionsFromPreviousMonth(Wallet $currentWallet, TransactionCategoryEnum $transactionCategoryEnum): void
    {
        $previousWallet = $this->walletService->findPreviousWallet($currentWallet->getIndividual(), $currentWallet->getYear(), $currentWallet->getMonth());

        if (!$previousWallet instanceof Wallet) {
            throw new NoPreviousWalletException();
        }

        $transactionCategoryId = $this->transactionCategoryRepository->findIdByCategoryName($transactionCategoryEnum->value);
        if (null === $transactionCategoryId) {
            throw new InvalidArgumentException(sprintf('No category found for %s', $transactionCategoryEnum->value));
        }

        $previousTransactions = $this->transactionRepository->findTransactionsByWalletAndCategory($previousWallet, $transactionCategoryId);

        if ([] === $previousTransactions) {
            throw new NoPreviousTransactionsException();
        }

        foreach ($previousTransactions as $transaction) {
            $newTransactionDate = $transaction->getDate();
            $newTransaction = new Transaction();
            $newTransaction->setAmount($transaction->getAmount());
            $newTransaction->setBudget($transaction->getBudget());
            $newTransaction->setDate(
                $newTransactionDate ? new DateTimeImmutable(sprintf(
                    '%d-%02d-%02d',
                    $currentWallet->getYear(),
                    $currentWallet->getMonth(),
                    (int) $newTransactionDate->format('d')
                )) : null
            );
            $newTransaction->setWallet($currentWallet);
            $newTransaction->setTransactionCategory($transaction->getTransactionCategory());
            $newTransaction->setNature($transaction->getNature());

            foreach ($transaction->getTag() as $tag) {
                $newTransaction->addTag($tag);
            }

            $this->entityManager->persist($newTransaction);
        }

        $this->entityManager->flush();
    }

    /**
     * Copies the remaining budget from the previous month's wallet to the current wallet.
     *
     * @param Wallet $currentWallet the wallet for the current month
     *
     * @throws NoPreviousWalletException          if no wallet is found for the previous month
     * @throws WalletNotFoundWithinLimitException
     */
    public function copyLeftToSpendFromPreviousMonth(Wallet $currentWallet): void
    {
        $previousWallet = $this->walletService->findPreviousWallet($currentWallet->getIndividual(), $currentWallet->getYear(), $currentWallet->getMonth());
        if (!$previousWallet instanceof Wallet) {
            throw new NoPreviousWalletException();
        }

        $transactionInfoDto = $this->getAllTransactionInformationByUser($previousWallet);
        $totalLeftToSpend = $transactionInfoDto->getTotalLeftToSpend();

        $this->transactionManager->copyTransactionsFromPreviousMonth($currentWallet, $totalLeftToSpend);
    }
}
