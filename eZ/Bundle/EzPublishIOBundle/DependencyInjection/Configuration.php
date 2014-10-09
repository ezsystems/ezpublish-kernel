<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var ConfigurationFactory[]
     */
    private $metadataHandlerFactories = array();

    /**
     * @var ConfigurationFactory[]
     */
    private $binarydataHandlerFactories = array();

    public function setMetadataHandlerFactories( array $factories )
    {
        $this->metadataHandlerFactories = $factories;
    }

    public function setBinarydataHandlerFactories( array $factories )
    {
        $this->binarydataHandlerFactories = $factories;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'ez_io' );

        $this->addHandlersSection(
            $rootNode,
            'metadata_handlers',
            'Handlers for files metadata, that read & write files metadata (size, modification time...)',
            $this->metadataHandlerFactories
        );
        $this->addHandlersSection(
            $rootNode,
            'binarydata_handlers',
            'Handlers for files binary data. Reads & write files binary content',
            $this->binarydataHandlerFactories
        );

        $rootNode->children()->end();

        return $treeBuilder;
    }

    /**
     * @param NodeDefinition $node
     * @param                $name
     * @param string $info block info line
     * @param ConfigurationFactory[] $factories
     */
    private function addHandlersSection( NodeDefinition $node, $name, $info, array &$factories )
    {
        $handlersNodeBuilder = $node
            ->children()
                ->arrayNode( $name )
                    ->info( $info )
                    ->useAttributeAsKey( 'name' )
                    ->prototype( 'array' )
                    ->performNoDeepMerging()
                    ->children();

        foreach ( $factories as $name => $factory )
        {
            $factoryNode = $handlersNodeBuilder->arrayNode( $name )->canBeUnset();
            $factory->addConfiguration( $factoryNode );
        }
    }
}
