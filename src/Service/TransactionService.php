<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Exception\NoPreviousBudgetException;
use App\Exception\NoPreviousTransactionsException;
use App\Manager\TransactionManager;
use App\Repository\TransactionRepository;
use App\Util\BudgetHelper;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;

final readonly class TransactionService
{
    public function __construct(private TransactionManager $transactionManager, private EntityManagerInterface $entityManager, private BudgetService $budgetService, private TransactionRepository $transactionRepository, private BudgetHelper $budgetHelper) {}

    /**
     * @return array<string, mixed> Returns an array of transactions with the type per categories
     */
    public function getAllTransactionInformationByUser(Budget $budget): array
    {
        return $this->transactionManager->getAllTransactionInformationByUser($budget);
    }

    /**
     * Copy transactions from the previous month to the current month's budget.
     *
     * @param Budget $currentBudget         the current budget to which transactions will be copied
     * @param int    $transactionCategoryId the ID of the transaction category to copy
     *
     * @throws NoPreviousBudgetException
     * @throws NoPreviousTransactionsException
     * @throws Exception
     */
    public function copyTransactionsFromPreviousMonth(?Budget $currentBudget, int $transactionCategoryId): void
    {
        if (!$currentBudget instanceof Budget) {
            throw new InvalidArgumentException();
        }

        $previousMonthData = $this->budgetHelper->getPreviousMonthAndYear($currentBudget->getYear(), $currentBudget->getMonth());
        $previousBudget = $this->budgetService->getBudgetByUser($currentBudget->getIndividual(), $previousMonthData['year'], $previousMonthData['month']);
        if (!$previousBudget instanceof Budget) {
            throw new NoPreviousBudgetException();
        }

        $previousTransactions = $this->transactionRepository->findTransactionsByBudgetAndCategory($previousBudget, $transactionCategoryId);
        if ([] === $previousTransactions) {
            throw new NoPreviousTransactionsException();
        }

        foreach ($previousTransactions as $transaction) {
            $newTransaction = new Transaction();
            $newTransaction->setDescription($transaction->getDescription());
            $newTransaction->setAmount($transaction->getAmount());
            $newTransaction->setDate(new DateTimeImmutable());
            $newTransaction->setBudget($currentBudget);
            $newTransaction->setTransactionCategory($transaction->getTransactionCategory());
            $newTransaction->setCategory($transaction->getCategory());

            $this->entityManager->persist($newTransaction);
        }

        $this->entityManager->flush();
    }

    public function copyLeftToSpendFromPreviousMonth(Budget $currentBudget): void
    {
        $previousMonthData = $this->budgetHelper->getPreviousMonthAndYear($currentBudget->getYear(), $currentBudget->getMonth());
        $previousBudget = $this->budgetService->getBudgetByUser($currentBudget->getIndividual(), $previousMonthData['year'], $previousMonthData['month']);
        if (!$previousBudget instanceof Budget) {
            throw new NoPreviousBudgetException();
        }

        $transactions = $this->getAllTransactionInformationByUser($previousBudget);
        $totalLeftToSpend = $transactions['totalRemaining'];

        $this->transactionManager->copyTransactionsFromPreviousMonth($currentBudget, $totalLeftToSpend);
    }
}
