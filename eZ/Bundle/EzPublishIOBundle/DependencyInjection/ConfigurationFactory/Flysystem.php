<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Configuration factory for the flysystem metadata and binarydata handlers.
 *
 * Since they share the same configuration, the same factory can be used.
 */
class Flysystem implements ConfigurationFactory
{
    public function addConfiguration( NodeDefinition $node )
    {
        $node
            ->info(
                'Handler based on league/flysystem, an abstract filesystem library. ' .
                'Yes, the metadata handler and binarydata handler look the same; it is NOT a mistake :)'
            )
            ->children()
                ->scalarNode( 'adapter' )
                    ->info(
                        "Flysystem adapter identifier. Should be configured using oneup flysystem bundle. " .
                        "Yes, the same adapter can be used for a binarydata and metadata handler"
                    )
                    ->isRequired()
                    ->example( 'nfs' )
                ->end()
            ->end();
    }
}
