<?php

declare(strict_types=1);

namespace Unit\Repository;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\Wallet;
use App\Repository\Test\InMemoryTransactionRepository;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

class InMemoryTransactionRepositoryTest extends TestCase
{
    private const string TRANSACTION_DESCRIPTION = 'Payment for services';

    private const float TRANSACTION_AMOUNT = 100.50;

    private const string TRANSACTION_DATE = '2024-09-15';

    private const string TRANSACTION_NATURE = 'Service';

    public function testSaveAndFindTransaction(): void
    {
        $wallet = new Wallet();
        $transactionCategory = new TransactionCategory();

        $transactionRepository = new InMemoryTransactionRepository();

        $transaction = new Transaction();
        $transaction->setDescription(self::TRANSACTION_DESCRIPTION)
            ->setAmount(self::TRANSACTION_AMOUNT)
            ->setDate(new DateTime(self::TRANSACTION_DATE))
            ->setNature(self::TRANSACTION_NATURE)
            ->setWallet($wallet)
            ->setTransactionCategory($transactionCategory);

        $transactionRepository->save($transaction);

        $foundTransaction = $transactionRepository->findOneBy(['description' => self::TRANSACTION_DESCRIPTION]);

        $this->assertNotNull($foundTransaction);
        $this->assertSame(self::TRANSACTION_AMOUNT, $foundTransaction->getAmount());
        $this->assertSame(self::TRANSACTION_DATE, $foundTransaction->getDate()->format('Y-m-d'));
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
        $transaction->setDescription(self::TRANSACTION_DESCRIPTION)
            ->setAmount(self::TRANSACTION_AMOUNT)
            ->setDate(new DateTime(self::TRANSACTION_DATE))
            ->setNature(self::TRANSACTION_NATURE)
            ->setWallet($wallet)
            ->setTransactionCategory($transactionCategory);

        $transactionRepository->save($transaction);

        $this->assertNotNull($transactionRepository->findOneBy(['description' => self::TRANSACTION_DESCRIPTION]));

        $transactionRepository->delete($transaction);

        $this->assertNull($transactionRepository->findOneBy(['description' => self::TRANSACTION_DESCRIPTION]));
    }

    public function testUpdateTransaction(): void
    {
        $wallet = new Wallet();
        $transactionCategory = new TransactionCategory();

        $transactionRepository = new InMemoryTransactionRepository();

        $transaction = new Transaction();
        $transaction->setDescription(self::TRANSACTION_DESCRIPTION)
            ->setAmount(self::TRANSACTION_AMOUNT)
            ->setDate(new DateTime(self::TRANSACTION_DATE))
            ->setNature(self::TRANSACTION_NATURE)
            ->setWallet($wallet)
            ->setTransactionCategory($transactionCategory);

        $transactionRepository->save($transaction);

        $foundTransaction = $transactionRepository->findOneBy(['description' => self::TRANSACTION_DESCRIPTION]);
        $this->assertNotNull($foundTransaction);

        $transaction->setAmount(self::TRANSACTION_AMOUNT);
        $transactionRepository->save($transaction);

        $foundTransaction = $transactionRepository->findOneBy(['description' => self::TRANSACTION_DESCRIPTION]);

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
