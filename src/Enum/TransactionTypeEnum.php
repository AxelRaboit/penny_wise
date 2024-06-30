<?php

namespace App\Enum;

class TransactionTypeEnum
{
    public const string INCOME = 'income';
    public const string EXPENSE = 'expense';
    public const string SAVINGS = 'savings';
    public const string BILLS = 'bills';
    public const string DEBT = 'debt';

    public const array ALLOWED_VALUES = [
        self::INCOME,
        self::EXPENSE,
        self::SAVINGS,
        self::BILLS,
        self::DEBT
    ];

    public function __construct(private string $value)
    {
        if (!in_array($value, self::ALLOWED_VALUES, true)) {
            throw new \InvalidArgumentException("Invalid value '$value' for enum TransactionTypeEnum");
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

    public function __toString(): string
    {
        return $this->value;
    }

    public static function getAllowedValues(): array
    {
        return self::ALLOWED_VALUES;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
