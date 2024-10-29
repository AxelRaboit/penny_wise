<?php

declare(strict_types=1);

namespace App\Util;

use function Symfony\Component\String\u;

final readonly class StringHelper
{
    /**
     * Check if the given string is empty.
     */
    public static function isEmpty(?string $string): bool
    {
        return '' === $string || '0' === $string || null === $string;
    }

    /**
     * Check if the given string is not empty.
     */
    public static function isNotEmpty(?string $string): bool
    {
        return !self::isEmpty($string);
    }

    /**
     * Normalize the given string: trim, lowercase, and title case each word.
     */
    public static function normalize(?string $string): ?string
    {
        return self::isNotEmpty($string) ? u($string)->trim()->lower()->title()->toString() : null;
    }
}
