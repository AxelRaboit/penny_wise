<?php

namespace App\Util;

final readonly class StringHelper
{
    /**
     * Check if the given string is empty.
     *
     * @param string $string
     * @return bool
     */
    public static function isEmpty(string $string): bool
    {
        return '' === $string || '0' === $string;
    }
}