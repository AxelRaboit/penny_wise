<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Manager\TransactionManager;
use App\Repository\TransactionRepository;
use App\Util\BudgetHelper;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

final readonly class TransactionService
{
    public function __construct(private TransactionManager $transactionManager, private EntityManagerInterface $entityManager, private BudgetService $budgetService, private TransactionRepository $transactionRepository, private BudgetHelper $budgetHelper){}

    /**
     * @param Budget $budget
     * @return array<string, mixed> Returns an array of transactions with the type per categories
     */
    public function getAllTransactionInformationByUser(Budget $budget): array
    {
        return $this->transactionManager->getAllTransactionInformationByUser($budget);
    }

    /**
     * Copy transactions from the previous month to the current month's budget.
     *
     * @param Budget $currentBudget The current budget to which transactions will be copied.
     * @param int $transactionCategoryId The ID of the transaction category to copy.
     * @return bool Returns true if transactions were copied, false if there were no transactions to copy.
     * @throws Exception
     */
    public function copyTransactionsFromPreviousMonth(Budget $currentBudget, int $transactionCategoryId): bool
    {
        $previousMonthData = $this->budgetHelper->getPreviousMonthAndYear($currentBudget->getYear(), $currentBudget->getMonth());
        $previousBudget = $this->budgetService->getBudgetByUser($currentBudget->getIndividual(), $previousMonthData['year'], $previousMonthData['month']);
        $previousTransactions = $this->transactionRepository->findTransactionsByBudgetAndCategory($previousBudget, $transactionCategoryId);

        if (empty($previousTransactions)) {
            return false;
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

        return true;
    }
}
