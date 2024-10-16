<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\Wallet;
use App\Enum\Wallet\CurrencyEnum;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Override;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private User $user;

    private Wallet $wallet;

    /**
     * @var array<TransactionCategory>
     */
    private array $transactionCategories = [];

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher) {}

    /**
     * @throws DateMalformedStringException
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->createUser($manager);
        $this->createTransactionCategory($manager);
        $this->createWallet($manager);
        $this->createTransaction($manager);
    }

    private function createTransactionCategory(ObjectManager $manager): void
    {
        $transactionCategories = [
            'incomes',
            'expenses',
            'bills',
            'debts',
            'savings',
        ];

        foreach ($transactionCategories as $categoryName) {
            $transactionCategory = new TransactionCategory();
            $transactionCategory->setName($categoryName);
            $manager->persist($transactionCategory);

            $this->transactionCategories[$categoryName] = $transactionCategory;
        }

        $manager->flush();
    }

    private function createUser(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@spendingwise.com');
        $user->setPassword($this->userPasswordHasher->hashPassword(
            $user,
            'user@spendingwise.com'
        ));
        $user->setActive(true);
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setUpdatedAt(new DateTimeImmutable());
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);

        $userInformation = new UserInformation();
        $userInformation->setUser($user);

        $manager->persist($userInformation);

        $this->user = $user;

        $manager->flush();
    }

    /**
     * @throws DateMalformedStringException
     */
    private function createWallet(ObjectManager $manager): void
    {
        $currentDate = new DateTimeImmutable();
        $year = (int) $currentDate->format('Y');
        $month = (int) $currentDate->format('m');

        $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
        $endDate = $startDate->modify('last day of this month');

        $wallet = new Wallet();
        $wallet->setUser($this->user);
        $wallet->setYear($year);
        $wallet->setMonth($month);
        $wallet->setCurrency(CurrencyEnum::EUR);
        $wallet->setStartBalance(2499.00);
        $wallet->setSpendingLimit(null);
        $wallet->setStartDate($startDate);
        $wallet->setEndDate($endDate);
        $wallet->setCreatedAt(new DateTimeImmutable());
        $wallet->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($wallet);

        $this->wallet = $wallet;

        $manager->flush();
    }

    private function createTransaction(ObjectManager $manager): void
    {
        $transaction = new Transaction();
        $transaction->setAmount(49.99);
        $transaction->setDate(new DateTimeImmutable());
        $transaction->setNature('Service');
        $transaction->setWallet($this->wallet);

        $transactionCategory = $this->transactionCategories['expenses'];
        $transaction->setTransactionCategory($transactionCategory);

        $transaction->setCreatedAt(new DateTimeImmutable());
        $transaction->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($transaction);

        $manager->flush();
    }
}
