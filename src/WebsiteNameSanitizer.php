<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle;

final class WebsiteNameSanitizer
{
    private const MAX_LENGTH = 200;

    public static function sanitize(string $value, string $default): string
    {
        $t = trim($value);
        if ('' === $t || \strlen($t) > self::MAX_LENGTH) {
            return $default;
        }

        return $t;
    }
}
