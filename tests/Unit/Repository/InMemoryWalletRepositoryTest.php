<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\Test\InMemoryWalletRepository;
use DateTime;
use Exception;
use LogicException;
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
            ->setMonth(self::MONTH_SEPTEMBER)
            ->setStartDate(new DateTime(self::START_DATE))
            ->setEndDate(new DateTime(self::END_DATE))
            ->setUser($user);

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
            ->setMonth(self::MONTH_SEPTEMBER)
            ->setStartDate(new DateTime(self::START_DATE))
            ->setEndDate(new DateTime(self::END_DATE))
            ->setUser($user);

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
            ->setMonth(self::MONTH_SEPTEMBER)
            ->setStartDate(new DateTime(self::START_DATE))
            ->setEndDate(new DateTime(self::END_DATE))
            ->setUser($user);
        $walletRepository->save($wallet);

        $foundWallet = $walletRepository->findOneBy(['year' => self::YEAR_2024, 'month' => self::MONTH_SEPTEMBER]);
        $this->assertNotNull($foundWallet);
        $this->assertSame(self::YEAR_2024, $foundWallet->getYear());
        $this->assertSame(self::MONTH_SEPTEMBER, $foundWallet->getMonth());
        $this->assertEquals(new DateTime(self::START_DATE), $foundWallet->getStartDate());
        $this->assertEquals(new DateTime(self::END_DATE), $foundWallet->getEndDate());

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
        $this->assertEquals($newStartDate->format('Y-m-d'), $foundUpdatedWallet->getStartDate()->format('Y-m-d'));
        $this->assertEquals($newEndDate->format('Y-m-d'), $foundUpdatedWallet->getEndDate()->format('Y-m-d'));

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

    public function testCreateDuplicateWalletFails(): void
    {
        $user = new User();
        $user->setEmail(self::EMAIL);

        $walletRepository = new InMemoryWalletRepository();

        $wallet1 = new Wallet();
        $wallet1->setYear(self::YEAR_2024)
            ->setMonth(self::MONTH_SEPTEMBER)
            ->setUser($user);

        $walletRepository->save($wallet1);

        $wallet2 = new Wallet();
        $wallet2->setYear(self::YEAR_2024)
            ->setMonth(self::MONTH_SEPTEMBER)
            ->setUser($user);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A wallet for the same year and month is already exists.');

        $walletRepository->save($wallet2);
    }
}
