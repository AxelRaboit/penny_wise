<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\User;
use App\Repository\BudgetRepository;

final readonly class BudgetService
{
    public function __construct(private BudgetRepository $budgetRepository){}

    public function getBudgetByUser(User $user, int $year, int $month): Budget
    {
        $budget = $this->budgetRepository
            ->findOneBy(['individual' => $user, 'year' => $year, 'month' => $month]);

        if (!$budget) {
            throw new \RuntimeException(`No budget found for user ${user}, year ${year} and month ${month}`);
        }

        return $budget;
    }
}