<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\User;
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

    private const string BUDGET_START_DATE = '2024-06-01';

    private const string BUDGET_END_DATE = '2024-06-30';

    private const int BUDGET_YEAR = 2024;

    private const float BUDGET_START_BALANCE = 1000.00;

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
        $budget = $this->createBudget();
        $this->entityManager->persist($budget);

        $transactionCategory = $this->createTransactionCategory();
        $this->save($transactionCategory);

        $transaction = new Transaction();
        $transaction->setCategory(self::CATEGORY_NAME);
        $transaction->setAmount(self::TRANSACTION_AMOUNT);
        $transaction->setDate(new DateTime(self::TRANSACTION_DATE));
        $transaction->setBudget($budget);
        $transaction->setTransactionCategory($transactionCategory);
        $this->save($transaction);

        $this->assertNotNull($transaction->getId());
        $this->assertNotNull($transaction->getAmount());
        $this->assertNotNull($transaction->getDate());
        $this->assertNotNull($transaction->getBudget());
        $this->assertNotNull($transaction->getCategory());
        $this->assertNotNull($transaction->getTransactionCategory());

        $this->assertSame($transaction->getId(), $transaction->getId());
        $this->assertSame(self::TRANSACTION_AMOUNT, $transaction->getAmount());
        $this->assertSame(new DateTime(self::TRANSACTION_DATE), $transaction->getDate());
        $this->assertSame($budget, $transaction->getBudget());
        $this->assertSame(self::CATEGORY_NAME, $transaction->getCategory());
        $this->assertSame($transactionCategory, $transaction->getTransactionCategory());
        $this->assertSame(self::BUDGET_START_BALANCE, $transaction->getBudget()->getStartBalance());
        $this->assertNotNull($transaction->getBudget()->getStartDate());
        $this->assertSame(self::BUDGET_START_DATE, $transaction->getBudget()->getStartDate()->format('Y-m-d'));
        $this->assertNotNull($transaction->getBudget()->getEndDate());
        $this->assertSame(self::BUDGET_END_DATE, $transaction->getBudget()->getEndDate()->format('Y-m-d'));
        $this->assertSame(self::CURRENCY, $transaction->getBudget()->getCurrency());
        $this->assertSame($this->getUser(), $transaction->getBudget()->getIndividual());

        $budget->addTransaction($transaction);

        $this->entityManager->flush();

        $this->assertCount(1, $budget->getTransactions());
        $this->assertTrue($budget->getTransactions()->contains($transaction));
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

    private function createBudget(): Budget
    {
        $budget = new Budget();
        $budget->setIndividual($this->getUser());
        $budget->setYear(self::BUDGET_YEAR);
        $budget->setMonth(MonthEnum::June);
        $budget->setStartDate(new DateTime(self::BUDGET_START_DATE));
        $budget->setEndDate(new DateTime(self::BUDGET_END_DATE));
        $budget->setCurrency(self::CURRENCY);
        $budget->setStartBalance(self::BUDGET_START_BALANCE);

        return $budget;
    }

    private function createTransactionCategory(): TransactionCategory
    {
        $transactionCategory = new TransactionCategory();
        $transactionCategory->setName(self::TRANSACTION_CATEGORY_NAME);

        return $transactionCategory;
    }
}
