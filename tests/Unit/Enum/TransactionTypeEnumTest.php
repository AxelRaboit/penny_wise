<?php

namespace App\Tests\Unit\Enum;

use App\Enum\TransactionTypeEnum;
use PHPUnit\Framework\TestCase;

class TransactionTypeEnumTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('incomes', TransactionTypeEnum::INCOMES()->getString());
        $this->assertEquals('expenses', TransactionTypeEnum::EXPENSES()->getString());
        $this->assertEquals('savings', TransactionTypeEnum::SAVINGS()->getString());
        $this->assertEquals('bills', TransactionTypeEnum::BILLS()->getString());
        $this->assertEquals('debts', TransactionTypeEnum::DEBTS()->getString());
    }

    public function testInvalidEnumValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TransactionTypeEnum(777);
    }
}