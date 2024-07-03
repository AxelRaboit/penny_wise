<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Manager\TransactionManager;

final readonly class TransactionService
{
    public function __construct(private TransactionManager $transactionManager){}

    /**
     * @param Budget $budget
     * @return array<string, array<string, array<int, Transaction>|string|float>>
     */
    public function getAllTransactionInformationByUser(Budget $budget): array
    {
        return $this->transactionManager->getAllTransactionInformationByUser($budget);
    }
}