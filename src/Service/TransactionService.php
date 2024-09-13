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
     * Copy bills from the previous month to the current month's budget.
     *
     * @param Budget $currentBudget The current budget to which bills will be copied.
     * @return void
     * @throws Exception
     */
    public function copyBillsFromPreviousMonth(Budget $currentBudget): void
    {
        $previousMonthData = $this->budgetHelper->getPreviousMonthAndYear($currentBudget->getYear(), $currentBudget->getMonth());
        $previousBudget = $this->budgetService->getBudgetByUser($currentBudget->getIndividual(), $previousMonthData['year'], $previousMonthData['month']);
        $previousBills = $this->transactionRepository->findBillsByBudget($previousBudget);

        foreach ($previousBills as $bill) {
            $newTransaction = new Transaction();
            $newTransaction->setDescription($bill->getDescription());
            $newTransaction->setAmount($bill->getAmount());
            $newTransaction->setDate(new DateTimeImmutable());
            $newTransaction->setBudget($currentBudget);
            $newTransaction->setTransactionCategory($bill->getTransactionCategory());
            $newTransaction->setCategory($bill->getCategory());

            $this->entityManager->persist($newTransaction);
        }

        $this->entityManager->flush();
    }
}
