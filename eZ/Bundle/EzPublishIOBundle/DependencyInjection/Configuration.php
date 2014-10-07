<?php

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root( 'ez_io' )
            ->children()
                ->append( $this->getMetaDataNode() )
                ->append( $this->getBinaryDataNode() )
            ->end();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function getBinaryDataNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root( 'binarydata_handlers' );

        $node
            ->children()
                ->arrayNode( 'flysystem' )
                    ->canBeUnset()
                    ->prototype( 'array' )
                        ->children()
                            ->scalarNode( 'adapter' )->isRequired()->info( 'flysystem adapter' )->example( 'nfs' )->end()
                            ->scalarNode( 'url_prefix' )->info( 'Prefix to append to url' )->example( 'http://static.example.com' )->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function getMetaDataNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root( 'metadata_handlers' );

        $node
            ->children()
                ->arrayNode( 'flysystem' )
                    ->canBeUnset()
                    ->prototype( 'array' )
                        ->children()
                            ->scalarNode( 'adapter' )->isRequired()->info( 'flysystem adapter' )->example( 'nfs' )->end()
                            ->scalarNode( 'url_prefix' )->info( 'Prefix to append to url' )->example( 'http://static.example.com' )->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode( 'legacy_dfs_cluster' )
                    ->canBeUnset()
                    ->prototype( 'array' )
                        ->children()
                            ->scalarNode( 'connection' )->info( 'doctrine connection' )->example( 'default' )->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
