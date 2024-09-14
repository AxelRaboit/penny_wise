<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Enum\MonthEnum;
use App\Exception\NoPreviousBudgetException;
use App\Exception\NoPreviousTransactionsException;
use App\Form\BudgetType;
use App\Manager\BudgetManager;
use App\Repository\BudgetRepository;
use App\Repository\LinkRepository;
use App\Repository\NoteRepository;
use App\Service\BudgetService;
use App\Service\TransactionService;
use App\Util\BudgetHelper;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BudgetController extends AbstractController
{
    private const string MONTHLY_BUDGET_TEMPLATE = 'budget/monthly.html.twig';

    private const string NEW_BUDGET_TEMPLATE = 'budget/new.html.twig';

    private const int BILL_CATEGORY_ID = 1;

    private const int EXPENSE_CATEGORY_ID = 2;

    public function __construct(
        private readonly TransactionService     $transactionService,
        private readonly BudgetService          $budgetService,
        private readonly EntityManagerInterface $entityManager,
        private readonly BudgetRepository       $budgetRepository,
        private readonly NoteRepository         $noteRepository,
        private readonly LinkRepository         $linkRepository,
        private readonly BudgetManager          $budgetManager,
        private readonly BudgetHelper           $budgetHelper,
    ){}

    /**
     * @throws Exception
     */
    #[Route('/budget/{year}/{month}', name: 'monthly_budget')]
    public function monthlyBudget(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        $budget = $this->budgetService->getBudgetByUser($user, $year, $month);
        if (!$budget instanceof Budget) {
            throw $this->createNotFoundException('Budget not found');
        }

        $transactions = $this->transactionService->getAllTransactionInformationByUser($budget);
        $yearWithMonths = $this->budgetRepository->getYearWithMonths($year);
        $notesFromBudget = $this->noteRepository->getNotesFromBudget($budget);
        /** @var array<string, array<string, array<Transaction>>> $transactions */
        $leftToSpendChart = $this->budgetService->createLeftToSpendChart($transactions);
        $totalSpendingForCurrentAndPreviousNthMonthsChart = $this->budgetService->createTotalSpendingForCurrentAndPreviousNthMonthsChart($year, $month, 3);
        $userLinks = $this->linkRepository->findByIndividual($user);
        $totalSpendingYearsChart = $this->budgetService->createTotalSpendingForCurrentAndAdjacentYearsChart();

        $options = [
            'userLinks' => $userLinks,
            'leftToSpendChart' => $leftToSpendChart,
            'totalSpendingForCurrentAndPreviousNthMonthsChart' => $totalSpendingForCurrentAndPreviousNthMonthsChart,
            'totalSpendingYearsChart' => $totalSpendingYearsChart,
            'budget' => $budget,
            'notesFromBudget' => $notesFromBudget,
            'yearWithMonths' => $yearWithMonths,
            'transactionCategories' => $transactions['transactionCategories'],
            'totalIncomesAndStartingBalance' => $transactions['totalIncomesAndStartingBalance'],
            'totalIncomes' => $transactions['totalIncomes'],
            'totalBills' => $transactions['totalBills'],
            'totalExpenses' => $transactions['totalExpenses'],
            'totalDebts' => $transactions['totalDebts'],
            'totalRemaining' => $transactions['totalRemaining'],
            'totalSpending' => $transactions['totalSpending'],
            'currentYear' => $year,
            'currentMonth' => $month,
        ];

        return $this->render(self::MONTHLY_BUDGET_TEMPLATE, $options);
    }

    /**
     * @throws Exception
     */
    #[Route('/budget/{year}/{month}/copy-bills', name: 'copy_previous_month_bills')]
    public function copyPreviousMonthBills(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentBudget = $this->budgetService->getBudgetByUser($user, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentBudget, self::BILL_CATEGORY_ID);
            $this->addFlash('success', 'Expenses copied successfully from the previous month.');
        } catch (NoPreviousBudgetException|NoPreviousTransactionsException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_budget', [
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/budget/{year}/{month}/copy-expenses', name: 'copy_previous_month_expenses')]
    public function copyPreviousMonthExpenses(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentBudget = $this->budgetService->getBudgetByUser($user, $year, $month);

        try {
            $this->transactionService->copyTransactionsFromPreviousMonth($currentBudget, self::EXPENSE_CATEGORY_ID);
            $this->addFlash('success', 'Expenses copied successfully from the previous month.');
        } catch (NoPreviousBudgetException|NoPreviousTransactionsException $exception) {
            $this->addFlash('warning', $exception->getMessage());
        }

        return $this->redirectToRoute('monthly_budget', [
            'year' => $year,
            'month' => $month,
        ]);
    }

    #[Route('/budget/new', name: 'budget_new')]
    public function new(Request $request): Response
    {
        $budget = new Budget();
        $form = $this->createForm(BudgetType::class, $budget);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $budget->setIndividual($user);
            $this->entityManager->persist($budget);
            $this->entityManager->flush();

            return $this->redirectToRoute('monthly_budget', [
                'year' => $budget->getYear(),
                'month' => $budget->getMonth(),
            ]);
        }

        return $this->render(self::NEW_BUDGET_TEMPLATE, [
            'form' => $form,
        ]);
    }

    #[Route('/budget/create-next/{year}/{month}', name: 'create_next_month_budget')]
    public function createNextMonthBudget(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        try {
            $nextMonth = $this->budgetHelper->getNextMonthAndYear($year, $month);
            $nextYear = $nextMonth['year'];
            $nextMonthEnum = MonthEnum::from($nextMonth['month']);

            $existingBudget = $this->budgetService->getBudgetByUser($user, $nextYear, $nextMonthEnum->value);
            if ($existingBudget !== null) {
                $this->addFlash(
                    'warning',
                    sprintf('Budget already exists for %s %d.', $nextMonthEnum->getName(), $nextYear)
                );
                return $this->redirectToRoute('monthly_budget', [
                    'year' => $nextYear,
                    'month' => $nextMonthEnum->value
                ]);
            }

            $this->budgetManager->createBudgetForMonth($user, $nextYear, $nextMonthEnum);

            $this->addFlash('success', sprintf('Budget for %s %d created successfully.', $nextMonthEnum->getName(), $nextYear));

        } catch (Exception $e) {
            $this->addFlash('error', sprintf('An error occurred while creating the budget: %s', $e->getMessage()));

            return $this->redirectToRoute('monthly_budget', ['year' => $year, 'month' => $month]);
        }

        return $this->redirectToRoute('monthly_budget', ['year' => $nextYear, 'month' => $nextMonthEnum->value]);
    }

    #[Route('/budget/create-previous/{year}/{month}', name: 'create_previous_month_budget')]
    public function createPreviousMonthBudget(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        try {
            $previousMonth = $this->budgetHelper->getPreviousMonthAndYear($year, $month);
            $previousYear = $previousMonth['year'];
            $previousMonthEnum = MonthEnum::from($previousMonth['month']);

            $existingBudget = $this->budgetService->getBudgetByUser($user, $previousYear, $previousMonthEnum->value);
            if ($existingBudget !== null) {
                $this->addFlash(
                    'warning',
                    sprintf('Budget already exists for %s %d.', $previousMonthEnum->getName(), $previousYear)
                );
                return $this->redirectToRoute('monthly_budget', [
                    'year' => $previousYear,
                    'month' => $previousMonthEnum->value
                ]);
            }

            $this->budgetManager->createBudgetForMonth($user, $previousYear, $previousMonthEnum);

            $this->addFlash('success', sprintf('Budget for %s %d created successfully.', $previousMonthEnum->getName(), $previousYear));

        } catch (Exception $e) {
            $this->addFlash('error', sprintf('An error occurred while creating the budget: %s', $e->getMessage()));
            return $this->redirectToRoute('monthly_budget', ['year' => $year, 'month' => $month]);
        }

        return $this->redirectToRoute('monthly_budget', ['year' => $previousYear, 'month' => $previousMonthEnum->value]);
    }

}
