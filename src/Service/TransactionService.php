<?php

namespace App\Service;

use App\Entity\Budget;
use App\Manager\TransactionManager;

final readonly class TransactionService
{
    public function __construct(private TransactionManager $transactionManager){}

    public function getAllBudgetInformationByUser(Budget $budget): array
    {
        return $this->transactionManager->getAllBudgetInformationByUser($budget);
    }

}