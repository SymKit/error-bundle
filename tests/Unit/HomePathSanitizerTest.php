<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symkit\ErrorBundle\HomePathSanitizer;

final class HomePathSanitizerTest extends TestCase
{
    #[DataProvider('safePathsProvider')]
    public function testSanitizeKeepsSafePaths(string $input, string $expected): void
    {
        self::assertSame($expected, HomePathSanitizer::sanitize($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function safePathsProvider(): iterable
    {
        yield 'default slash' => ['/', '/'];
        yield 'trim' => ['  /app  ', '/app'];
        yield 'nested' => ['/foo/bar', '/foo/bar'];
        yield 'unicode segment' => ['/café/boisson', '/café/boisson'];
        yield 'with query' => ['/search?q=a', '/search?q=a'];
        yield 'with hash' => ['/page#section', '/page#section'];
    }

    #[DataProvider('unsafePathsProvider')]
    public function testSanitizeFallsBackToRootForUnsafePaths(string $input): void
    {
        self::assertSame('/', HomePathSanitizer::sanitize($input));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unsafePathsProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace' => ['   '];
        yield 'no leading slash' => ['app'];
        yield 'protocol relative' => ['//evil.example/path'];
        yield 'javascript' => ['javascript:alert(1)'];
        yield 'https url' => ['https://evil.example/'];
        yield 'colon in path' => ['/foo:bar'];
        yield 'double slash segment' => ['/foo//bar'];
        yield 'space in path' => ['/foo bar'];
        yield 'angle bracket' => ['/</script>'];
    }

    public function testSanitizeRejectsOverlongPaths(): void
    {
        $long = '/'.str_repeat('a', 2100);
        self::assertSame('/', HomePathSanitizer::sanitize($long));
    }
}
