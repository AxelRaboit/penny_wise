<?php

namespace App\Tests\Unit\Enum;

use App\Enum\TransactionTypeEnum;
use PHPUnit\Framework\TestCase;

class TransactionTypeEnumTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('Incomes', TransactionTypeEnum::INCOMES()->getString());
        $this->assertEquals('Expenses', TransactionTypeEnum::EXPENSES()->getString());
        $this->assertEquals('Savings', TransactionTypeEnum::SAVINGS()->getString());
        $this->assertEquals('Bills', TransactionTypeEnum::BILLS()->getString());
        $this->assertEquals('Debts', TransactionTypeEnum::DEBTS()->getString());
    }

    public function testInvalidEnumValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TransactionTypeEnum(777);
    }
}