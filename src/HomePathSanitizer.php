<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle;

/**
 * Normalizes {@see SymkitErrorBundle} {@code home_path} to a safe same-origin relative URL:
 * must start with a single {@code /}, no protocol-relative or pseudo-schemes in the path segment.
 */
final class HomePathSanitizer
{
    private const DEFAULT = '/';

    private const MAX_LENGTH = 2048;

    /**
     * Path segment (before {@code ?} or {@code #}): safe app-relative path only.
     */
    private const PATH_SEGMENT_PATTERN = '#^/(?!//)(?:[\p{L}\p{N}_\-.~]++/)*[\p{L}\p{N}_\-.~]*$#u';

    public static function sanitize(string $value): string
    {
        $trimmed = trim($value);
        if ('' === $trimmed || \strlen($trimmed) > self::MAX_LENGTH) {
            return self::DEFAULT;
        }

        $pathEnd = \strlen($trimmed);
        foreach (['?', '#'] as $marker) {
            $p = strpos($trimmed, $marker);
            if (false !== $p) {
                $pathEnd = min($pathEnd, $p);
            }
        }

        $pathOnly = substr($trimmed, 0, $pathEnd);
        $suffix = substr($trimmed, $pathEnd);

        if (1 === preg_match(self::PATH_SEGMENT_PATTERN, $pathOnly)) {
            return $trimmed;
        }

        return self::DEFAULT;
    }
}
