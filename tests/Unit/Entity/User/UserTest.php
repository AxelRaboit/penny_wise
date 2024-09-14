<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\User;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private const string USER_EMAIL = 'axel@example.com';

    private const string USER_PASSWORD = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm';

    private const string ROLE_USER = 'ROLE_USER';

    private const string ROLE_ADMIN = 'ROLE_ADMIN';

    private const array USER_ROLES = [self::ROLE_USER, self::ROLE_ADMIN];

    public function testGettersAndSetters(): void
    {
        $user = new User();

        $user->setEmail(self::USER_EMAIL);
        $this->assertSame(self::USER_EMAIL, $user->getEmail());

        $user->setRoles(self::USER_ROLES);
        $this->assertSame(self::USER_ROLES, $user->getRoles());

        $user->setPassword(self::USER_PASSWORD);
        $this->assertSame(self::USER_PASSWORD, $user->getPassword());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail(self::USER_EMAIL);
        $this->assertSame(self::USER_EMAIL, $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->eraseCredentials();
        $this->assertTrue(true);
    }
}
