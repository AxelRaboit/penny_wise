<?php

namespace App\Tests\Unit\Enum;

use App\Enum\TransactionTypeEnum;
use PHPUnit\Framework\TestCase;

class TransactionTypeEnumTest extends TestCase
{
    public function testEnumValues()
    {
        $this->assertEquals('income', TransactionTypeEnum::INCOME()->getValue());
        $this->assertEquals('expense', TransactionTypeEnum::EXPENSE()->getValue());
        $this->assertEquals('savings', TransactionTypeEnum::SAVINGS()->getValue());
        $this->assertEquals('bills', TransactionTypeEnum::BILLS()->getValue());
        $this->assertEquals('debt', TransactionTypeEnum::DEBT()->getValue());
    }

    public function testInvalidEnumValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        new TransactionTypeEnum('invalid');
    }
}