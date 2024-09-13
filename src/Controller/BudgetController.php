<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\BudgetType;
use App\Repository\BudgetRepository;
use App\Repository\LinkRepository;
use App\Repository\NotificationRepository;
use App\Service\BudgetService;
use App\Service\TransactionService;
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
        private readonly NotificationRepository $notificationRepository,
        private readonly LinkRepository          $linkRepository,
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
            throw new Exception('User not found');
        }

        $budget = $this->budgetService->getBudgetByUser($user, $year, $month);
        $transactions = $this->transactionService->getAllTransactionInformationByUser($budget);
        $yearWithMonths = $this->budgetRepository->getYearWithMonths($year);
        $lastNthNotificationsFromBudget = $this->notificationRepository->getNotificationsFromBudget($budget);
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
            'lastNthNotificationsFromBudget' => $lastNthNotificationsFromBudget,
            'yearWithMonths' => $yearWithMonths,
            'transactionCategories' => $transactions['transactionCategories'],
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
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        $currentBudget = $this->budgetService->getBudgetByUser($user, $year, $month);
        $response = $this->transactionService->copyTransactionsFromPreviousMonth($currentBudget, self::BILL_CATEGORY_ID);

        if (!$response) {
            $this->addFlash('warning', 'No bills found to copy from the previous month.');
        } else {
            $this->addFlash('success', 'Bills copied successfully from the previous month.');
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
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        $currentBudget = $this->budgetService->getBudgetByUser($user, $year, $month);
        $response = $this->transactionService->copyTransactionsFromPreviousMonth($currentBudget, self::EXPENSE_CATEGORY_ID);
        if (!$response) {
            $this->addFlash('warning', 'No expenses found to copy from the previous month.');
        } else {
            $this->addFlash('success', 'Expenses copied successfully from the previous month.');
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
}
