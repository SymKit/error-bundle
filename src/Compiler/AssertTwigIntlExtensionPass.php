<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twig\Extra\Intl\IntlExtension;

/**
 * Ensures the host registers Twig Intl when the bundle is enabled and Twig is used.
 * The bundle does not register {@see IntlExtension} itself.
 */
final class AssertTwigIntlExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('symkit_error.enabled')) {
            return;
        }
        if (true !== $container->getParameter('symkit_error.enabled')) {
            return;
        }
        if (!$container->hasDefinition('twig') && !$container->hasAlias('twig')) {
            return;
        }
        if (!class_exists(IntlExtension::class)) {
            throw new LogicException('Symkit Error Bundle is enabled and Twig is in use: install twig/intl-extra so error templates can use Intl filters. Run: composer require twig/intl-extra');
        }
        foreach ($container->findTaggedServiceIds('twig.extension') as $id => $tags) {
            if (!$container->hasDefinition($id)) {
                continue;
            }
            $def = $container->findDefinition($id);
            if (IntlExtension::class === $def->getClass()) {
                return;
            }
        }

        throw new LogicException('Symkit Error Bundle is enabled and Twig is in use: register the Twig Intl extension. After composer require twig/intl-extra, either enable it in config/packages/twig.yaml:'."\n\n    twig:\n        extra:\n            intl: true\n\n".'(often via symfony/twig-pack), or define a service for '.IntlExtension::class.' tagged with "twig.extension".');
    }
}
