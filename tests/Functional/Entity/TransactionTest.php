<?php

namespace App\Tests\Functional\Entity;

use App\Entity\Transaction;
use App\Enum\TransactionTypeEnum;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function testTransactionType()
    {
        $transaction = new Transaction();
        $transaction->setType(TransactionTypeEnum::INCOME());

        $this->assertEquals('income', $transaction->getType()->getValue());
    }
}