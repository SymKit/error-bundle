<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symkit\ErrorBundle\WebsiteNameSanitizer;

final class WebsiteNameSanitizerTest extends TestCase
{
    public function testTrimAndKeepValidName(): void
    {
        self::assertSame('Acme', WebsiteNameSanitizer::sanitize('  Acme  ', 'Fallback'));
    }

    public function testEmptyFallsBackToDefault(): void
    {
        self::assertSame('Fallback', WebsiteNameSanitizer::sanitize('', 'Fallback'));
        self::assertSame('Fallback', WebsiteNameSanitizer::sanitize('   ', 'Fallback'));
    }

    public function testOverlongFallsBackToDefault(): void
    {
        $long = str_repeat('x', 250);
        self::assertSame('Fallback', WebsiteNameSanitizer::sanitize($long, 'Fallback'));
    }
}
