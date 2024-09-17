<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\CurrencyEnum;
use App\Enum\MonthEnum;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransactionTest extends KernelTestCase
{
    private const string USER_EMAIL = 'test@gmail.com';

    private const string CURRENCY = 'EUR';

    private const string WALLET_START_DATE = '2024-06-01';

    private const string WALLET_END_DATE = '2024-06-30';

    private const int WALLET_YEAR = 2024;

    private const float WALLET_START_BALANCE = 1000.00;

    private const string CATEGORY_NAME = 'Test Category';

    private const string TRANSACTION_CATEGORY_NAME = 'Test Transaction Category';

    private const float TRANSACTION_AMOUNT = 100.00;

    private const string TRANSACTION_DATE = '2024-06-01';

    private EntityManagerInterface $entityManager;

    #[Override]
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $doctrine = $kernel->getContainer()->get('doctrine');

        if (!$doctrine instanceof ManagerRegistry) {
            throw new RuntimeException('Doctrine service is not of type ManagerRegistry');
        }

        $entityManager = $doctrine->getManager();

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new RuntimeException('Entity manager is not of type EntityManagerInterface');
        }

        $this->entityManager = $entityManager;
    }

    public function testTransactionType(): void
    {
        $wallet = $this->createWallet();
        $this->entityManager->persist($wallet);

        $transactionCategory = $this->createTransactionCategory();
        $this->save($transactionCategory);

        $transaction = new Transaction();
        $transaction->setNature(self::CATEGORY_NAME);
        $transaction->setAmount(self::TRANSACTION_AMOUNT);
        $transaction->setDate(new DateTime(self::TRANSACTION_DATE));
        $transaction->setWallet($wallet);
        $transaction->setTransactionCategory($transactionCategory);
        $this->save($transaction);

        $this->assertNotNull($transaction->getId());
        $this->assertNotNull($transaction->getAmount());
        $this->assertNotNull($transaction->getDate());
        $this->assertNotNull($transaction->getWallet());
        $this->assertNotNull($transaction->getNature());
        $this->assertNotNull($transaction->getTransactionCategory());

        $this->assertSame($transaction->getId(), $transaction->getId());
        $this->assertSame(self::TRANSACTION_AMOUNT, $transaction->getAmount());
        $this->assertSame(new DateTime(self::TRANSACTION_DATE), $transaction->getDate());
        $this->assertSame($wallet, $transaction->getWallet());
        $this->assertSame(self::CATEGORY_NAME, $transaction->getNature());
        $this->assertSame($transactionCategory, $transaction->getTransactionCategory());
        $this->assertSame(self::WALLET_START_BALANCE, $transaction->getWallet()->getStartBalance());
        $this->assertNotNull($transaction->getWallet()->getStartDate());
        $this->assertSame(self::WALLET_START_DATE, $transaction->getWallet()->getStartDate()->format('Y-m-d'));
        $this->assertNotNull($transaction->getWallet()->getEndDate());
        $this->assertSame(self::WALLET_END_DATE, $transaction->getWallet()->getEndDate()->format('Y-m-d'));
        $this->assertSame(self::CURRENCY, $transaction->getWallet()->getCurrency());
        $this->assertSame($this->getUser(), $transaction->getWallet()->getIndividual());

        $wallet->addTransaction($transaction);

        $this->entityManager->flush();

        $this->assertCount(1, $wallet->getTransactions());
        $this->assertTrue($wallet->getTransactions()->contains($transaction));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    private function getUser(): User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => self::USER_EMAIL]);

        if (null === $user) {
            $user = new User();
            $user->setEmail(self::USER_EMAIL);
            $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }

    private function save(object $object): void
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    private function createWallet(): Wallet
    {
        $wallet = new Wallet();
        $wallet->setIndividual($this->getUser());
        $wallet->setYear(self::WALLET_YEAR);
        $wallet->setMonth(MonthEnum::June);
        $wallet->setStartDate(new DateTime(self::WALLET_START_DATE));
        $wallet->setEndDate(new DateTime(self::WALLET_END_DATE));
        $wallet->setCurrency(CurrencyEnum::EUR);
        $wallet->setStartBalance(self::WALLET_START_BALANCE);

        return $wallet;
    }

    private function createTransactionCategory(): TransactionCategory
    {
        $transactionCategory = new TransactionCategory();
        $transactionCategory->setName(self::TRANSACTION_CATEGORY_NAME);

        return $transactionCategory;
    }
}
