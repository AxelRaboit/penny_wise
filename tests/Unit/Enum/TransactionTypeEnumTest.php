<?php

namespace App\Tests\Unit\Enum;

use App\Enum\TransactionTypeEnum;
use PHPUnit\Framework\TestCase;

class TransactionTypeEnumTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('incomes', TransactionTypeEnum::INCOMES()->getValue());
        $this->assertEquals('expenses', TransactionTypeEnum::EXPENSES()->getValue());
        $this->assertEquals('savings', TransactionTypeEnum::SAVINGS()->getValue());
        $this->assertEquals('bills', TransactionTypeEnum::BILLS()->getValue());
        $this->assertEquals('debts', TransactionTypeEnum::DEBTS()->getValue());
    }

    public function testInvalidEnumValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TransactionTypeEnum('invalid');
    }
}