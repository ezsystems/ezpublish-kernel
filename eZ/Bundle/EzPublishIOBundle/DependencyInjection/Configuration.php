<?php

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'ez_io' );

        $this->addMetadataHandlersSection( $rootNode );
        $this->addBinarydataHandlersSection( $rootNode );

        $rootNode->children()->end();

        return $treeBuilder;
    }

    private function addMetadataHandlersSection( NodeDefinition $node )
    {
        $metadataHandlersNodeBuilder = $node
            ->children()
                ->arrayNode( 'metadata_handlers' )
                    ->info( 'Handlers for files metadata, that read & write files metadata (size, modification time...)' )
                    ->useAttributeAsKey( 'name' )
                    ->prototype( 'array' )
                    ->performNoDeepMerging()
                    ->children();

        $this->addFlysystemHandlerConfiguration( $metadataHandlersNodeBuilder );
    }

    private function addBinarydataHandlersSection( NodeDefinition $node )
    {
        $metadataHandlersNodeBuilder = $node
            ->children()
                ->arrayNode( 'binarydata_handlers' )
                    ->info( 'Handlers for files binary, that read & write binary content' )
                    ->useAttributeAsKey( 'name' )
                    ->prototype( 'array' )
                    ->performNoDeepMerging()
                    ->children();

        $this->addFlysystemHandlerConfiguration( $metadataHandlersNodeBuilder );
    }

    private function addFlysystemHandlerConfiguration( NodeBuilder $node )
    {
        $node
            ->arrayNode( 'flysystem' )
            ->info( 'Handler based on league/flysystem, an abstract filesystem library.' )
            ->canBeUnset()
            ->children()
                ->scalarNode( 'adapter' )
                    ->info( "Flysystem adapter. Should be configured using oneup flysystem bundle.")
                    ->isRequired()
                    ->example( 'nfs' )
                ->end()
            ->end()
        ->end();

        return $node;
    }
}
