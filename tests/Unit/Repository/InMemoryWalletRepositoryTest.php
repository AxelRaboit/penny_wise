<?php

declare(strict_types=1);

namespace Unit\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use App\Enum\MonthEnum;
use App\Repository\Test\InMemoryWalletRepository;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

class InMemoryWalletRepositoryTest extends TestCase
{
    private const int MONTH_SEPTEMBER = 9;

    private const int YEAR_2024 = 2024;

    private const string START_DATE = '2024-09-01';

    private const string END_DATE = '2024-09-30';

    private const string EMAIL = 'user@example.com';

    private const string NEW_START_DATE = '2024-09-05';

    private const string NEW_END_DATE = '2024-09-25';

    private const int NON_EXISTENT_YEAR = 2025;

    private const int NON_EXISTENT_MONTH = 10;

    private const string NON_EXISTENT_START_DATE = '2024-09-01';

    private const string NON_EXISTENT_END_DATE = '2024-09-30';

    public function testSaveAndFindWallet(): void
    {
        $user = new User();
        $user->setEmail(self::EMAIL);

        $walletRepository = new InMemoryWalletRepository();

        $wallet = new Wallet();
        $wallet->setYear(self::YEAR_2024)
            ->setMonth(MonthEnum::from(self::MONTH_SEPTEMBER))
            ->setStartDate(new DateTime(self::START_DATE))
            ->setEndDate(new DateTime(self::END_DATE))
            ->setIndividual($user);

        $walletRepository->save($wallet);

        $foundWallet = $walletRepository->findOneBy(['year' => self::YEAR_2024, 'month' => self::MONTH_SEPTEMBER]);

        $this->assertNotNull($foundWallet);
        $this->assertSame(self::YEAR_2024, $foundWallet->getYear());
        $this->assertSame(self::MONTH_SEPTEMBER, $foundWallet->getMonth());
    }

    public function testDeleteWallet(): void
    {
        $user = new User();
        $user->setEmail(self::EMAIL);

        $walletRepository = new InMemoryWalletRepository();

        $wallet = new Wallet();
        $wallet->setYear(self::YEAR_2024)
            ->setMonth(MonthEnum::from(self::MONTH_SEPTEMBER))
            ->setStartDate(new DateTime(self::START_DATE))
            ->setEndDate(new DateTime(self::END_DATE))
            ->setIndividual($user);

        $walletRepository->save($wallet);

        $this->assertNotNull($walletRepository->findOneBy(['year' => self::YEAR_2024, 'month' => self::MONTH_SEPTEMBER]));

        $walletRepository->delete($wallet);

        $this->assertNull($walletRepository->findOneBy(['year' => self::YEAR_2024, 'month' => self::MONTH_SEPTEMBER]));
    }

    public function testUpdateWallet(): void
    {
        $user = new User();
        $user->setEmail(self::EMAIL);

        $walletRepository = new InMemoryWalletRepository();

        $wallet = new Wallet();
        $wallet->setYear(self::YEAR_2024)
            ->setMonth(MonthEnum::from(self::MONTH_SEPTEMBER))
            ->setStartDate(new DateTime(self::START_DATE))
            ->setEndDate(new DateTime(self::END_DATE))
            ->setIndividual($user);

        $walletRepository->save($wallet);

        $foundWallet = $walletRepository->findOneBy(['year' => self::YEAR_2024, 'month' => self::MONTH_SEPTEMBER]);
        $this->assertNotNull($foundWallet);
        $this->assertSame(self::YEAR_2024, $foundWallet->getYear());
        $this->assertSame(self::MONTH_SEPTEMBER, $foundWallet->getMonth());
        $this->assertSame(new DateTime(self::START_DATE), $foundWallet->getStartDate());

        $newStartDate = new DateTime(self::NEW_START_DATE);
        $newEndDate = new DateTime(self::NEW_END_DATE);
        $foundWallet->setStartDate($newStartDate);
        $foundWallet->setEndDate($newEndDate);

        $walletRepository->save($foundWallet);

        $foundUpdatedWallet = $walletRepository->findOneBy([
            'year' => self::YEAR_2024,
            'month' => self::MONTH_SEPTEMBER,
            'startDate' => $newStartDate,
            'endDate' => $newEndDate,
        ]);
        $this->assertNotNull($foundUpdatedWallet);
        $this->assertSame(self::NEW_START_DATE, $foundUpdatedWallet->getStartDate()->format('Y-m-d'));
        $this->assertSame(self::NEW_END_DATE, $foundUpdatedWallet->getEndDate()->format('Y-m-d'));

        $oldWallet = $walletRepository->findOneBy([
            'year' => self::YEAR_2024,
            'month' => self::MONTH_SEPTEMBER,
            'startDate' => new DateTime(self::START_DATE),
            'endDate' => new DateTime(self::END_DATE),
        ]);
        $this->assertNull($oldWallet, 'Le portefeuille avec les anciennes dates ne doit plus exister.');
    }

    public function testUpdateNonExistentWallet(): void
    {
        $walletRepository = new InMemoryWalletRepository();

        $nonExistentWallet = $walletRepository->findOneBy([
            'year' => self::NON_EXISTENT_YEAR,
            'month' => self::NON_EXISTENT_MONTH,
            'startDate' => new DateTime(self::NON_EXISTENT_START_DATE),
            'endDate' => new DateTime(self::NON_EXISTENT_END_DATE),
        ]);

        $this->assertNull($nonExistentWallet);
        $this->expectException(Exception::class);
        throw new Exception('Wallet does not exist');
    }
}
