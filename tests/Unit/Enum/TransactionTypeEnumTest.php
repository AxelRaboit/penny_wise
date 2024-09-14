<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\TransactionTypeEnum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TransactionTypeEnumTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('Incomes', TransactionTypeEnum::INCOMES()->getString());
        $this->assertSame('Expenses', TransactionTypeEnum::EXPENSES()->getString());
        $this->assertSame('Savings', TransactionTypeEnum::SAVINGS()->getString());
        $this->assertSame('Bills', TransactionTypeEnum::BILLS()->getString());
        $this->assertSame('Debts', TransactionTypeEnum::DEBTS()->getString());
    }

    public function testInvalidEnumValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TransactionTypeEnum(777);
    }
}
