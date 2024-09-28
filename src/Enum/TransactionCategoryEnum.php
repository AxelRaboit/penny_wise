<?php

declare(strict_types=1);

namespace App\Enum;

enum TransactionCategoryEnum: string
{
    case Incomes = 'incomes';
    case Expenses = 'expenses';
    case Savings = 'savings';
    case Bills = 'bills';
    case Debts = 'debts';
}