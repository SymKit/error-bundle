<?php

declare(strict_types=1);

namespace Symkit\ErrorBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SymkitErrorBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Enable the bundle to override Symfony default error pages with custom templates.')
                ->end()
                ->scalarNode('website_name')
                    ->defaultValue('Symkit')
                    ->info('The name of the website to display in error pages.')
                ->end()
                ->scalarNode('home_path')
                    ->defaultValue('/')
                    ->info('URL or path to the homepage (used for "Back to homepage" and footer links).')
                ->end()
            ->end()
        ;
    }

    /** @param array{enabled: bool, website_name: string, home_path: string} $config */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter('symkit_error.enabled', $config['enabled']);
        $builder->setParameter('symkit_error.website_name', $config['website_name']);
        $builder->setParameter('symkit_error.home_path', $config['home_path']);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $configs = $builder->getExtensionConfig('symkit_error');
        $enabled = true;
        $websiteName = 'Symkit';
        $homePath = '/';
        foreach ($configs as $config) {
            if (isset($config['enabled'])) {
                $enabled = $config['enabled'];
            }
            if (isset($config['website_name'])) {
                $websiteName = $config['website_name'];
            }
            if (isset($config['home_path'])) {
                $homePath = $config['home_path'];
            }
        }

        if (!$enabled) {
            return;
        }

        $builder->prependExtensionConfig('twig', [
            'paths' => [
                $this->getPath().'/templates/bundles/TwigBundle' => 'Twig',
            ],
        ]);

        $builder->prependExtensionConfig('twig', [
            'globals' => [
                'symkit_error_website_name' => $websiteName,
                'symkit_error_home_path' => $homePath,
            ],
        ]);
    }
}
