<?php

namespace App\Enum;

class TransactionTypeEnum
{
    public const int INCOMES = 4;
    public const int EXPENSES = 2;
    public const int SAVINGS = 5;
    public const int BILLS = 1;
    public const int DEBTS = 3;

    /**
     * @var array<int, string> $valueToString
     */
    private static array $valueToString = [
        self::INCOMES => 'Incomes',
        self::EXPENSES => 'Expenses',
        self::SAVINGS => 'Savings',
        self::BILLS => 'Bills',
        self::DEBTS => 'Debts',
    ];

    /**
     * @var array<string, int> $stringToValue
     */
    private static array $stringToValue = [
        'Incomes' => self::INCOMES,
        'Expenses' => self::EXPENSES,
        'Savings' => self::SAVINGS,
        'Bills' => self::BILLS,
        'Debts' => self::DEBTS,
    ];

    public function __construct(private readonly int $value)
    {
        if (!isset(self::$valueToString[$value])) {
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

    public function getValue(): int
    {
        return $this->value;
    }

    public function getString(): string
    {
        return self::$valueToString[$this->value];
    }

    public function __toString(): string
    {
        return $this->getString();
    }

    public static function fromString(string $value): self
    {
        if (!isset(self::$stringToValue[$value])) {
            throw new \InvalidArgumentException("Invalid string '$value' for enum TransactionTypeEnum");
        }
        return new self(self::$stringToValue[$value]);
    }

    /**
     * @return int[]
     */
    public static function getAllowedValues(): array
    {
        return array_keys(self::$valueToString);
    }
}
