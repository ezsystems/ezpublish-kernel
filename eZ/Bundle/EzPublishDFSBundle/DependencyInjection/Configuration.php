<?php

namespace eZ\Bundle\EzPublishDFSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $handlersNode = $treeBuilder
            ->root( 'ez_dfs' )
                ->useAttributeAsKey('name')
                ->prototype( 'array' )
                    ->children()
                        ->append( $this->getBinaryDataNode() )
                        ->append( $this->getMetadataNode() )
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getBinaryDataNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root( 'binarydata' );

        $node
            ->children()
                ->arrayNode( 'flysystem' )
                    ->canBeUnset()
                    ->children()
                        ->scalarNode( 'adapter' )->info( 'flysystem adapter' )->example( 'nfs' )->end()
                    ->end()
                ->end()
                ->arrayNode( 'filesystem' )
                    ->canBeUnset()
                    ->children()
                        ->scalarNode( 'root' )->info( 'path to the root directory' )->example( '/mnt/nfs' )->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getMetaDataNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root( 'metadata' );

        $node
            ->children()
                ->arrayNode( 'legacy_dfs_cluster' )
                    ->canBeUnset()
                    ->children()
                        ->scalarNode( 'connection' )->info( 'doctrine connection' )->example( 'default' )->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
