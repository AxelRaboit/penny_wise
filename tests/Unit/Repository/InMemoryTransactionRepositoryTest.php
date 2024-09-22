<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\Wallet;
use App\Repository\Test\InMemoryTransactionRepository;
use DateTime;
use DateTimeInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class InMemoryTransactionRepositoryTest extends TestCase
{
    private const float TRANSACTION_AMOUNT = 100.50;

    private const string TRANSACTION_DATE = '2024-09-15';

    private const string TRANSACTION_NATURE = 'Service';

    public function testSaveAndFindTransaction(): void
    {
        $wallet = new Wallet();
        $transactionCategory = new TransactionCategory();

        $transactionRepository = new InMemoryTransactionRepository();

        $transaction = new Transaction();
        $transaction
            ->setAmount(self::TRANSACTION_AMOUNT)
            ->setDate(new DateTime(self::TRANSACTION_DATE))
            ->setNature(self::TRANSACTION_NATURE)
            ->setWallet($wallet)
            ->setTransactionCategory($transactionCategory);

        $transactionRepository->save($transaction);

        $foundTransaction = $transactionRepository->findOneBy(['nature' => self::TRANSACTION_NATURE]);

        $this->assertNotNull($foundTransaction);
        $this->assertSame(self::TRANSACTION_AMOUNT, $foundTransaction->getAmount());

        $foundDate = $foundTransaction->getDate();
        if ($foundDate instanceof DateTimeInterface) {
            $this->assertSame(self::TRANSACTION_DATE, $foundDate->format('Y-m-d'));
        }

        $this->assertSame(self::TRANSACTION_NATURE, $foundTransaction->getNature());
        $this->assertSame($wallet, $foundTransaction->getWallet());
        $this->assertSame($transactionCategory, $foundTransaction->getTransactionCategory());
    }

    public function testDeleteTransaction(): void
    {
        $wallet = new Wallet();
        $transactionCategory = new TransactionCategory();

        $transactionRepository = new InMemoryTransactionRepository();

        $transaction = new Transaction();
        $transaction
            ->setAmount(self::TRANSACTION_AMOUNT)
            ->setDate(new DateTime(self::TRANSACTION_DATE))
            ->setNature(self::TRANSACTION_NATURE)
            ->setWallet($wallet)
            ->setTransactionCategory($transactionCategory);

        $transactionRepository->save($transaction);

        $this->assertNotNull($transactionRepository->findOneBy(['nature' => self::TRANSACTION_NATURE]));

        $transactionRepository->delete($transaction);

        $this->assertNull($transactionRepository->findOneBy(['nature' => self::TRANSACTION_NATURE]));
    }

    public function testUpdateTransaction(): void
    {
        $wallet = new Wallet();
        $transactionCategory = new TransactionCategory();

        $transactionRepository = new InMemoryTransactionRepository();

        $transaction = new Transaction();
        $transaction
            ->setAmount(self::TRANSACTION_AMOUNT)
            ->setDate(new DateTime(self::TRANSACTION_DATE))
            ->setNature(self::TRANSACTION_NATURE)
            ->setWallet($wallet)
            ->setTransactionCategory($transactionCategory);

        $transactionRepository->save($transaction);

        $foundTransaction = $transactionRepository->findOneBy(['nature' => self::TRANSACTION_NATURE]);
        $this->assertNotNull($foundTransaction);

        $transaction->setAmount(self::TRANSACTION_AMOUNT);
        $transactionRepository->save($transaction);

        $foundTransaction = $transactionRepository->findOneBy(['nature' => self::TRANSACTION_NATURE]);

        $this->assertNotNull($foundTransaction);
        $this->assertSame(self::TRANSACTION_AMOUNT, $foundTransaction->getAmount());
    }

    public function testUpdateNonExistentTransaction(): void
    {
        $transactionRepository = new InMemoryTransactionRepository();

        $nonExistentTransaction = $transactionRepository->findOneBy(['description' => 'Non-existing Transaction']);

        $this->assertNull($nonExistentTransaction);
        $this->expectException(Exception::class);
        throw new Exception('Transaction does not exist');
    }
}
