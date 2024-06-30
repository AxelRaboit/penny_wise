<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\User;
use App\Enum\TransactionTypeEnum;
use App\Repository\BudgetRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class BudgetService
{
    public function __construct(private readonly  BudgetRepository $budgetRepository){}

    public function getBudgetByUser(UserInterface|User $user, int $year, int $month): Budget
    {
        return $this->budgetRepository
            ->findOneBy(['individual' => $user, 'year' => $year, 'month' => $month]);
    }
}