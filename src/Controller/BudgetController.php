<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\BudgetType;
use App\Repository\BudgetRepository;
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
    public function __construct(
        private readonly TransactionService     $transactionService,
        private readonly BudgetService          $budgetService,
        private readonly EntityManagerInterface $entityManager,
        private readonly BudgetRepository       $budgetRepository,
        private readonly NotificationRepository $notificationRepository,
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
        $lastNthNotifications = $this->notificationRepository->getLastNthNotifications(5);
        /** @var array<string, array<string, array<Transaction>>> $transactions */
        $leftToSpendChart = $this->budgetService->createLeftToSpendChart($transactions);
        $totalSpendingForCurrentAndPreviousNthMonthsChart = $this->budgetService->createTotalSpendingForCurrentAndPreviousNthMonthsChart($year, $month, 3);

        $options = [
            'leftToSpendChart' => $leftToSpendChart,
            'totalSpendingForCurrentAndPreviousNthMonthsChart' => $totalSpendingForCurrentAndPreviousNthMonthsChart,
            'budget' => $budget,
            'lastNthNotifications' => $lastNthNotifications,
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
            'form' => $form->createView(),
        ]);
    }
}
