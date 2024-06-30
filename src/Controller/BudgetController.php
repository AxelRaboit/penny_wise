<?php

namespace App\Controller;

use App\Service\BudgetService;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BudgetController extends AbstractController
{
    public function __construct(private readonly TransactionService $transactionService, private readonly BudgetService $budgetService){}

    #[Route('/budget/{year}/{month}', name: 'monthly_budget')]
    public function monthlyBudget($year, $month): Response
    {
        $user = $this->getUser();
        $budget = $this->budgetService->getBudgetByUser($user, $year, $month);
        $transactions = $this->transactionService->getAllBudgetInformationByUser($budget);

        return $this->render('budget/monthly.html.twig', [
            'budget' => $budget,
            'transactions' => $transactions,
        ]);
    }
}