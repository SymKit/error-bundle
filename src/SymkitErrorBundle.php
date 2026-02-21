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
                ->scalarNode('website_name')
                    ->defaultValue('Symkit')
                    ->info('The name of the website to display in error pages.')
                ->end()
            ->end()
        ;
    }

    /** @param array{website_name: string} $config */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter('symkit_error.website_name', $config['website_name']);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('twig', [
            'paths' => [
                $this->getPath().'/templates/bundles/TwigBundle' => 'Twig',
            ],
        ]);

        $configs = $builder->getExtensionConfig('symkit_error');
        $websiteName = 'Symkit';
        foreach ($configs as $config) {
            if (isset($config['website_name'])) {
                $websiteName = $config['website_name'];
            }
        }

        $builder->prependExtensionConfig('twig', [
            'globals' => [
                'website_name' => $websiteName,
            ],
        ]);
    }
}
