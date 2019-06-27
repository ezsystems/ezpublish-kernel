<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use ArrayObject;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /** @var ConfigurationFactory[]|ArrayObject */
    private $metadataHandlerFactories = [];

    /** @var ConfigurationFactory[]|ArrayObject */
    private $binarydataHandlerFactories = [];

    public function setMetadataHandlerFactories(ArrayObject $factories)
    {
        $this->metadataHandlerFactories = $factories;
    }

    public function setBinarydataHandlerFactories(ArrayObject $factories)
    {
        $this->binarydataHandlerFactories = $factories;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ez_io');

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
     * @param ConfigurationFactory[]|ArrayObject $factories
     */
    private function addHandlersSection(NodeDefinition $node, $name, $info, ArrayObject $factories)
    {
        $handlersNodeBuilder = $node
            ->children()
                ->arrayNode($name)
                    ->info($info)
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->performNoDeepMerging()
                    ->children();

        foreach ($factories as $name => $factory) {
            $factoryNode = $handlersNodeBuilder->arrayNode($name)->canBeUnset();
            $factory->addConfiguration($factoryNode);
        }
    }
}
