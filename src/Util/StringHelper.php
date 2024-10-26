<?php

declare(strict_types=1);

namespace App\Util;

final readonly class StringHelper
{
    /**
     * Check if the given string is empty.
     */
    public static function isEmpty(string $string): bool
    {
        return '' === $string || '0' === $string;
    }
}
