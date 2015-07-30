<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected $rootNodeName;

    public function __construct($rootNodeName)
    {
        $this->rootNodeName = $rootNodeName;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->rootNodeName);

        $this->addConnectionsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addConnectionsSection(ArrayNodeDefinition $node)
    {
        $node->children()
            ->scalarNode('default_connection')
                ->info('Name of the default connection')
            ->end()
            ->arrayNode('connections')
                ->info('Elasticsearch Search Engine connections configuration')
                ->useAttributeAsKey('connection_name')
                ->performNoDeepMerging()
                ->prototype('array')
                    ->children()
                        ->scalarNode('server')
                            ->isRequired()
                            ->info('Address of the Elasticsearch server')
                            ->example('https://username:password@hostname.com/path:1234')
                        ->end()
                        ->scalarNode('index_name')
                            ->defaultValue('ezpublish')
                            ->info('Name of Elasticsearch index')
                        ->end()
                        ->arrayNode('document_type_name')
                            ->info('Document type names')
                            ->children()
                                ->scalarNode('content')
                                    ->defaultValue('content')
                                    ->info('Name of the Content document type')
                                ->end()
                                ->scalarNode('location')
                                    ->defaultValue('location')
                                    ->info('Name of the Location document type')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
