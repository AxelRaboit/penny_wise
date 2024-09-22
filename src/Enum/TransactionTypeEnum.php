<?php

declare(strict_types=1);

namespace App\Enum;

use InvalidArgumentException;

enum TransactionTypeEnum
{
    case INCOMES;
    case EXPENSES;
    case SAVINGS;
    case BILLS;
    case DEBTS;

    public function getString(): string
    {
        return match ($this) {
            self::INCOMES => 'incomes',
            self::EXPENSES => 'expenses',
            self::SAVINGS => 'savings',
            self::BILLS => 'bills',
            self::DEBTS => 'debts',
        };
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            'incomes' => self::INCOMES,
            'expenses' => self::EXPENSES,
            'savings' => self::SAVINGS,
            'bills' => self::BILLS,
            'debts' => self::DEBTS,
            default => throw new InvalidArgumentException(sprintf("Invalid string '%s' for enum TransactionTypeEnum", $value)),
        };
    }
}
