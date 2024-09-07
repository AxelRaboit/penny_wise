<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\BudgetType;
use App\Service\BudgetService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
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
    ){}

    #[Route('/budget/{year}/{month}', name: 'monthly_budget')]
    public function monthlyBudget(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user instanceof User) {
            throw new \RuntimeException('User not found');
        }

        $budget = $this->budgetService->getBudgetByUser($user, $year, $month);
        $transactions = $this->transactionService->getAllTransactionInformationByUser($budget);
        /** @var array<string, array<string, array<Transaction>>> $transactions */
        $chart = $this->budgetService->createBudgetChart($transactions);

        $options = [
            'chart' => $chart,
            'budget' => $budget,
            'transactionCategories' => $transactions['transactionCategories'],
            'totalIncomes' => $transactions['totalIncomes'],
            'totalBills' => $transactions['totalBills'],
            'totalExpenses' => $transactions['totalExpenses'],
            'totalDebts' => $transactions['totalDebts'],
            'totalRemaining' => $transactions['totalRemaining'],
            'totalSpending' => $transactions['totalSpending'],
        ];

        return $this->render(self::MONTHLY_BUDGET_TEMPLATE, $options);
    }

    #[Route('/budget/new', name: 'budget_new')]
    public function new(Request $request): Response
    {
        $budget = new Budget();
        $form = $this->createForm(BudgetType::class, $budget);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'form',
                'form' => $this->renderView(self::NEW_BUDGET_TEMPLATE, [
                    'form' => $form->createView(),
                ])
            ]);
        }

        return $this->render(self::NEW_BUDGET_TEMPLATE, [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/budget/new/submission', name: 'budget_new_submission', priority: 1)]
    public function newSubmission(Request $request): Response
    {
        $budget = new Budget();
        /** @var User $user */
        $user = $this->getUser();
        $budget->setIndividual($user);

        $form = $this->createForm(BudgetType::class, $budget);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($budget);

            try {
                $this->entityManager->flush();
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['status' => 'success']);
                } else {
                    return $this->redirectToRoute('app_homepage');
                }
            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['status' => 'error', 'message' => $e->getMessage()]);
                } else {
                    $this->addFlash('error', 'An error occurred while saving the budget: ' . $e->getMessage());
                    return $this->render(self::NEW_BUDGET_TEMPLATE, [
                        'form' => $form->createView(),
                    ]);
                }
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'form',
                'form' => $this->renderView(self::NEW_BUDGET_TEMPLATE, [
                    'form' => $form->createView(),
                ])
            ]);
        }

        return $this->render(self::NEW_BUDGET_TEMPLATE, [
            'form' => $form->createView(),
        ]);
    }
}
