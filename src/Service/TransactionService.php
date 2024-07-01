<?php

namespace App\Service;

use App\Entity\Budget;
use App\Manager\TransactionManager;

final readonly class TransactionService
{
    public function __construct(private TransactionManager $transactionManager){}

    /**
     * Get all budget information by user.
     *
     * @param Budget $budget The budget entity.
     * @return array<array<string, mixed>> The array of budget information.
     */
    public function getAllTransactionInformationByUser(Budget $budget): array
    {
        return $this->transactionManager->getAllTransactionInformationByUser($budget);
    }
}