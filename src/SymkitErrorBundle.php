<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symkit\ErrorBundle\Compiler\AssertTwigIntlExtensionPass;

class SymkitErrorBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new AssertTwigIntlExtensionPass());
    }

    private const DEFAULT_ENABLED = true;

    private const DEFAULT_WEBSITE_NAME = 'Symkit';

    private const DEFAULT_HOME_PATH = '/';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('enabled')
                    ->defaultValue(self::DEFAULT_ENABLED)
                    ->info('Enable the bundle to override Symfony default error pages with custom templates.')
                ->end()
                ->scalarNode('website_name')
                    ->defaultValue(self::DEFAULT_WEBSITE_NAME)
                    ->info('The name of the website to display in error pages.')
                ->end()
                ->scalarNode('home_path')
                    ->defaultValue(self::DEFAULT_HOME_PATH)
                    ->info('App-relative path only (e.g. / or /dashboard): single leading slash, no scheme, max 2048 chars. Unsafe values fall back to /.')
                ->end()
            ->end()
        ;
    }

    /**
     * @param array{enabled: bool, website_name?: mixed, home_path?: mixed} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $websiteName = \is_string($config['website_name'] ?? null)
            ? WebsiteNameSanitizer::sanitize($config['website_name'], self::DEFAULT_WEBSITE_NAME)
            : self::DEFAULT_WEBSITE_NAME;
        $homePath = \is_string($config['home_path'] ?? null)
            ? HomePathSanitizer::sanitize($config['home_path'])
            : HomePathSanitizer::sanitize('');

        $builder->setParameter('symkit_error.enabled', $config['enabled']);
        $builder->setParameter('symkit_error.website_name', $websiteName);
        $builder->setParameter('symkit_error.home_path', $homePath);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $merged = self::mergeRawExtensionConfigs(array_values($builder->getExtensionConfig('symkit_error')));

        if (!$merged['enabled']) {
            return;
        }

        $builder->prependExtensionConfig('twig', [
            'paths' => [
                $this->getPath().'/templates/bundles/TwigBundle' => 'Twig',
            ],
        ]);

        $websiteName = WebsiteNameSanitizer::sanitize($merged['website_name'], self::DEFAULT_WEBSITE_NAME);
        $homePath = HomePathSanitizer::sanitize($merged['home_path']);

        $builder->prependExtensionConfig('twig', [
            'globals' => [
                'symkit_error_website_name' => $websiteName,
                'symkit_error_home_path' => $homePath,
            ],
        ]);
    }

    /**
     * Merges raw extension config slices in registration order (later files override, unset keys unchanged).
     * Only keys defined in {@see configure()} are considered. Default values match the config tree defaults.
     *
     * @param list<array<string, mixed>> $configs
     *
     * @return array{enabled: bool, website_name: string, home_path: string}
     */
    private static function mergeRawExtensionConfigs(array $configs): array
    {
        $allowed = ['enabled' => true, 'website_name' => true, 'home_path' => true];
        $merged = [
            'enabled' => self::DEFAULT_ENABLED,
            'website_name' => self::DEFAULT_WEBSITE_NAME,
            'home_path' => self::DEFAULT_HOME_PATH,
        ];
        foreach ($configs as $config) {
            $merged = array_replace($merged, array_intersect_key($config, $allowed));
        }

        $websiteName = \is_string($merged['website_name'])
            ? $merged['website_name']
            : self::DEFAULT_WEBSITE_NAME;
        $homePath = \is_string($merged['home_path'])
            ? $merged['home_path']
            : self::DEFAULT_HOME_PATH;

        return [
            'enabled' => \is_bool($merged['enabled']) ? $merged['enabled'] : self::DEFAULT_ENABLED,
            'website_name' => $websiteName,
            'home_path' => $homePath,
        ];
    }
}
