<?php

namespace App\Enum;

class TransactionTypeEnum
{
    public const string INCOMES = 'incomes';
    public const string EXPENSES = 'expenses';
    public const string SAVINGS = 'savings';
    public const string BILLS = 'bills';
    public const string DEBTS = 'debts';

    /**
     * @var array<string>
     */
    public const array ALLOWED_VALUES = [
        self::INCOMES,
        self::EXPENSES,
        self::SAVINGS,
        self::BILLS,
        self::DEBTS,
    ];

    public function __construct(private readonly string $value)
    {
        if (!in_array($value, self::ALLOWED_VALUES, true)) {
            throw new \InvalidArgumentException("Invalid value '$value' for enum TransactionTypeEnum");
        }
    }

    public static function INCOMES(): self
    {
        return new self(self::INCOMES);
    }

    public static function EXPENSES(): self
    {
        return new self(self::EXPENSES);
    }

    public static function SAVINGS(): self
    {
        return new self(self::SAVINGS);
    }

    public static function BILLS(): self
    {
        return new self(self::BILLS);
    }

    public static function DEBTS(): self
    {
        return new self(self::DEBTS);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return string[]
     */
    public static function getAllowedValues(): array
    {
        return self::ALLOWED_VALUES;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
