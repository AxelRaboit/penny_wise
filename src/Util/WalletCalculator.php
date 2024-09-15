<?php

declare(strict_types=1);

namespace App\Util;

use App\Entity\Transaction;
use App\Entity\Wallet;

final readonly class WalletCalculator
{
    public function __construct(private TransactionCalculator $transactionCalculator) {}

    /**
     * @param array<Transaction> $transactions
     */
    public function calculateRemainingBalance(Wallet $wallet, array $transactions): float
    {
        dump($transactions);
        $totalIncomes = $this->transactionCalculator->calculateTotalIncomes($transactions);
        $totalSpending = $this->transactionCalculator->calculateTotalSpending($transactions);

        return $wallet->getStartBalance() + $totalIncomes - $totalSpending;
    }
}
