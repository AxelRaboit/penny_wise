<?php

declare(strict_types=1);

namespace App\Service\Checker\Transaction;

use App\Entity\Transaction;
use App\Repository\Transaction\TransactionRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class TransactionCheckerService
{
    public function __construct(private TransactionRepository $transactionRepository) {}

    /**
     * Returns the transaction or throws an exception if not found.
     */
    public function getTransactionOrThrow(int $id): Transaction
    {
        $transaction = $this->transactionRepository->find($id);
        if (!$transaction instanceof Transaction) {
            throw new NotFoundHttpException('Transaction not found.');
        }

        return $transaction;
    }

    /**
     * Returns the transaction or null if not found.
     */
    public function getTransactionOrNull(int $id): ?Transaction
    {
        return $this->transactionRepository->find($id);
    }
}
