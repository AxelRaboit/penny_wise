<?php

namespace App\Tests\Unit\Entity\Share;

use App\Entity\Share;
use PHPUnit\Framework\TestCase;

class ShareTest extends TestCase
{
    private const CONTENT = 'content';
    public function testGettersAndSetters(): void
    {
        $user = new Share();

        $user->setContent(self::CONTENT);
        $this->assertEquals(self::CONTENT, $user->getContent());
    }
}
