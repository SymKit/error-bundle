<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Tests\Support;

use Closure;
use Twig\Extra\Intl\IntlExtension;

/**
 * Minimal container config for integration tests that boot Twig + Symkit Error (enabled).
 */
final class TwigTestKernelConfig
{
    /**
     * @param callable(object): void|null $afterFramework
     */
    public static function frameworkTestWithTwigIntl(?callable $afterFramework = null): Closure
    {
        return static function (object $container) use ($afterFramework): void {
            $container->loadFromExtension('framework', ['test' => true]);
            $container->register('twig.extension.symkit_tests_intl', IntlExtension::class)
                ->addTag('twig.extension');
            if (null !== $afterFramework) {
                $afterFramework($container);
            }
        };
    }
}
