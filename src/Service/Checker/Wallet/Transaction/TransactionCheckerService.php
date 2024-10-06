<?php

declare(strict_types=1);

namespace App\Service\Checker\Wallet\Transaction;

use App\Entity\Transaction;
use App\Repository\Transaction\TransactionRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class TransactionCheckerService
{
    public function __construct(private readonly TransactionRepository $transactionRepository) {}

    public function getTransactionOrThrow(int $id): Transaction
    {
        $transaction = $this->transactionRepository->find($id);
        if (!$transaction instanceof Transaction) {
            throw new NotFoundHttpException('Transaction not found.');
        }

        return $transaction;
    }
}
