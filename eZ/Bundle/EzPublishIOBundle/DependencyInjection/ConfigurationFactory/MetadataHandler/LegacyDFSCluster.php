<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory\MetadataHandler;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition as ServiceDefinition;
use Symfony\Component\DependencyInjection\Reference;

class LegacyDFSCluster implements ConfigurationFactory
{
    public function getParentServiceId()
    {
        return 'ezpublish.core.io.metadata_handler.legacy_dfs_cluster';
    }

    public function configureHandler(ServiceDefinition $definition, array $config)
    {
        $definition->replaceArgument(0, new Reference($config['connection']));
    }

    public function addConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->info(
                'A MySQL based handler, compatible with the legacy DFS one, that stores metadata in the ezdfsfile table'
            )
            ->children()
                ->scalarNode('connection')
                    ->info('Doctrine connection service')
                    ->example('doctrine.dbal.cluster_connection')
                ->end()
            ->end();
    }
}
