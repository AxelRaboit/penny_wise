<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\BudgetService;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BudgetController extends AbstractController
{
    private const string MONTHLY_BUDGET_TEMPLATE = 'budget/monthly.html.twig';
    public function __construct(private readonly TransactionService $transactionService, private readonly BudgetService $budgetService){}

    #[Route('/budget/{year}/{month}', name: 'monthly_budget')]
    public function monthlyBudget(int $year, int $month): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user instanceof User) {
            throw new \RuntimeException('User not found');
        }

        $budget = $this->budgetService->getBudgetByUser($user, $year, $month);
        /** @var array<string, array<string, array<Transaction>>> $transactions */
        $transactions = $this->transactionService->getAllTransactionInformationByUser($budget);
        $remainingBalance = $this->budgetService->getRemainingBalance($budget, $transactions);
        $chart = $this->budgetService->createBudgetChart($budget, $transactions);

        $options = [
            'chart' => $chart,
            'budget' => $budget,
            'transactions' => $transactions,
            'remainingBalance' => $remainingBalance,
        ];

        return $this->render(self::MONTHLY_BUDGET_TEMPLATE, $options);
    }
}
