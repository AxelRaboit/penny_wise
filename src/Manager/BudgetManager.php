<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Budget;
use App\Entity\User;
use App\Enum\MonthEnum;
use App\Exception\NoPreviousBudgetException;
use App\Service\BudgetService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

final readonly class BudgetManager
{
    private const string EURO_CURRENCY = 'EUR';

    private const float START_BALANCE = 0.0;

    private const string LAST_DAY_OF_THIS_MONTH = 'last day of this month';

    public function __construct(private EntityManagerInterface $entityManager, private BudgetService $budgetService, private TransactionManager $transactionManager) {}

    /**
     * Create a budget for a given user, year, and month.
     *
     * @throws Exception
     */
    public function createBudgetForMonth(User $user, int $year, MonthEnum $monthEnum): void
    {
        $newBudget = new Budget();

        $firstDayOfMonth = sprintf('%d-%02d-01', $year, $monthEnum->value);
        $startDate = new DateTimeImmutable($firstDayOfMonth);
        $endDate = $startDate->modify(self::LAST_DAY_OF_THIS_MONTH);

        $newBudget->setStartDate($startDate);
        $newBudget->setEndDate($endDate);
        $newBudget->setIndividual($user);
        $newBudget->setYear($year);
        $newBudget->setMonth($monthEnum);
        $newBudget->setCurrency(self::EURO_CURRENCY);
        $newBudget->setStartBalance(self::START_BALANCE);

        $this->entityManager->persist($newBudget);
        $this->entityManager->flush();
    }

    public function deleteBudgetForMonth(User $user, int $year, int $month): void
    {
        $budget = $this->budgetService->getBudgetByUser($user, $year, $month);
        if (!$budget instanceof Budget) {
            throw new NoPreviousBudgetException();
        }

        $this->transactionManager->findAndDeleteTransactionsByBudget($budget);

        $this->entityManager->remove($budget);
        $this->entityManager->flush();
    }
}
