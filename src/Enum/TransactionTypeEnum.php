<?php

namespace App\Enum;

use InvalidArgumentException;

class TransactionTypeEnum
{
    private const INCOME = 'income';
    private const EXPENSE = 'expense';
    private const SAVINGS = 'savings';
    private const BILLS = 'bills';
    private const DEBT = 'debt';
    private const ALLOWED_VALUES = [
        self::INCOME,
        self::EXPENSE,
        self::SAVINGS,
        self::BILLS,
        self::DEBT
    ];

    public function __construct(private string $value)
    {
        if (!in_array($value, self::ALLOWED_VALUES, true)) {
            throw new InvalidArgumentException("Invalid value '$value' for enum TransactionTypeEnum");
        }
        $this->value = $value;
    }

    public static function INCOME(): self
    {
        return new self(self::INCOME);
    }

    public static function EXPENSE(): self
    {
        return new self(self::EXPENSE);
    }

    public static function SAVINGS(): self
    {
        return new self(self::SAVINGS);
    }

    public static function BILLS(): self
    {
        return new self(self::BILLS);
    }

    public static function DEBT(): self
    {
        return new self(self::DEBT);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function getAllowedValues(): array
    {
        return self::ALLOWED_VALUES;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
