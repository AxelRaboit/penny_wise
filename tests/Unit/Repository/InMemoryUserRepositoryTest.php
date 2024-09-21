<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\Test\InMemoryUserRepository;
use Exception;
use PHPUnit\Framework\TestCase;

class InMemoryUserRepositoryTest extends TestCase
{
    private const string USER_EMAIL = 'test@example.com';

    private const string USER_PASSWORD = 'password123';

    private const array USER_ROLE = ['ROLE_USER'];

    private const string NEW_USER_EMAIL = 'new@example.com';

    private const string NEW_USER_PASSWORD = 'newpassword123';

    public function testSaveAndFindUserByEmail(): void
    {
        $userRepository = new InMemoryUserRepository();

        $user = new User();
        $user->setEmail(self::USER_EMAIL);
        $user->setPassword(self::USER_PASSWORD);
        $user->setRoles(self::USER_ROLE);

        $userRepository->save($user);

        $foundUser = $userRepository->findOneBy(['email' => self::USER_EMAIL]);

        $this->assertNotNull($foundUser);
        $this->assertSame(self::USER_EMAIL, $foundUser->getEmail());
    }

    public function testDeleteUser(): void
    {
        $userRepository = new InMemoryUserRepository();

        $user = new User();
        $user->setEmail(self::USER_EMAIL);
        $user->setPassword(self::USER_PASSWORD);
        $user->setRoles(self::USER_ROLE);

        $userRepository->save($user);

        $this->assertNotNull($userRepository->findOneBy(['email' => self::USER_EMAIL]));

        $userRepository->delete($user);

        $this->assertNull($userRepository->findOneBy(['email' => self::USER_EMAIL]));
    }

    public function testUpdateUser(): void
    {
        $userRepository = new InMemoryUserRepository();

        $user = new User();
        $user->setEmail(self::USER_EMAIL);
        $user->setPassword(self::USER_PASSWORD);
        $user->setRoles(self::USER_ROLE);

        $userRepository->save($user);

        $foundUser = $userRepository->findOneBy(['email' => self::USER_EMAIL]);
        $this->assertNotNull($foundUser);
        $this->assertSame(self::USER_EMAIL, $foundUser->getEmail());
        $this->assertSame(self::USER_PASSWORD, $foundUser->getPassword());

        $user->setEmail(self::NEW_USER_EMAIL);
        $user->setPassword(self::NEW_USER_PASSWORD);

        $userRepository->save($user);

        $foundUpdatedUser = $userRepository->findOneBy(['email' => self::NEW_USER_EMAIL]);
        $this->assertNotNull($foundUpdatedUser);
        $this->assertSame(self::NEW_USER_EMAIL, $foundUpdatedUser->getEmail());
        $this->assertSame(self::NEW_USER_PASSWORD, $foundUpdatedUser->getPassword());

        $this->assertNull($userRepository->findOneBy(['email' => self::USER_EMAIL]));
    }

    public function testUpdateNonExistentUser(): void
    {
        $userRepository = new InMemoryUserRepository();

        $nonExistentUser = $userRepository->findOneBy(['email' => 'Non-existing User']);

        $this->assertNull($nonExistentUser);
        $this->expectException(Exception::class);
        throw new Exception('User does not exist');
    }
}
