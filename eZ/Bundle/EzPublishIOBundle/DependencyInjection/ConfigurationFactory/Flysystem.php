<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as ServiceDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Configuration factory for the flysystem metadata and binarydata handlers.
 *
 * Binarydata & metadata are identical, except for the parent service.
 */
abstract class Flysystem implements ConfigurationFactory, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function addConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->info(
                'Handler based on league/flysystem, an abstract filesystem library. ' .
                'Yes, the metadata handler and binarydata handler look the same; it is NOT a mistake :)'
            )
            ->children()
                ->scalarNode('adapter')
                    ->info(
                        'Flysystem adapter identifier. Should be configured using oneup flysystem bundle. ' .
                        'Yes, the same adapter can be used for a binarydata and metadata handler'
                    )
                    ->isRequired()
                    ->example('nfs')
                ->end()
            ->end();
    }

    public function configureHandler(ServiceDefinition $definition, array $config)
    {
        $filesystemId = $this->createFilesystem($this->container, $config['name'], $config['adapter']);
        $definition->replaceArgument(0, new Reference($filesystemId));
    }

    /**
     * Creates a flysystem filesystem $name service.
     *
     * @param ContainerBuilder $container
     * @param string $name filesystem name (nfs, local...)
     * @param string $adapter adapter name
     *
     * @return string
     */
    private function createFilesystem(ContainerBuilder $container, $name, $adapter)
    {
        $adapterId = sprintf('oneup_flysystem.%s_adapter', $adapter);
        if (!$container->hasDefinition($adapterId)) {
            throw new InvalidConfigurationException("Unknown flysystem adapter $adapter");
        }

        $filesystemId = sprintf('ezpublish.core.io.flysystem.%s_filesystem', $name);
        $definition = $container->setDefinition(
            $filesystemId,
            new DefinitionDecorator('ezpublish.core.io.flysystem.base_filesystem')
        );
        $definition->setArguments([new Reference($adapterId)]);

        return $filesystemId;
    }
}
