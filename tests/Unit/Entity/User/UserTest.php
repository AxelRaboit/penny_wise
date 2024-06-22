<?php

namespace App\Tests\Unit\Entity\User;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private const USER_EMAIL = 'axel@example.com';
    private const USER_PASSWORD = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm';
    private const ROLE_USER = 'ROLE_USER';
    private const ROLE_ADMIN = 'ROLE_ADMIN';
    private const USER_ROLES = [self::ROLE_USER, self::ROLE_ADMIN];
    public function testGettersAndSetters(): void
    {
        $user = new User();

        $user->setEmail(self::USER_EMAIL);
        $this->assertEquals(self::USER_EMAIL, $user->getEmail());

        $user->setRoles(self::USER_ROLES);
        $this->assertEquals(self::USER_ROLES, $user->getRoles());

        $user->setPassword(self::USER_PASSWORD);
        $this->assertEquals(self::USER_PASSWORD, $user->getPassword());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail(self::USER_EMAIL);
        $this->assertEquals(self::USER_EMAIL, $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->eraseCredentials();
        $this->assertTrue(true);
    }
}
