<?php
/**
 * File containing the Languages class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IO extends AbstractParser
{
    private $siteAccessesByLanguages = array();

    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'io' )
                ->info( 'Binary storage options' )
                ->children()
                    ->scalarNode( 'metadata_handler' )
                        ->info( 'handler uses to manipulate IO files metadata' )
                        ->example( 'default' )
                    ->end()
                    ->scalarNode( 'binarydata_handler' )
                        ->info( 'handler uses to manipulate IO files binarydata' )
                        ->example( 'default' )
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig( array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
    {
        if ( isset( $scopeSettings['metadata_handler'] ) )
        {
            $contextualizer->setContextualParameter( 'io.metadata_handler', $currentScope, $scopeSettings['metadata_handler'] );
        }
        if ( isset( $scopeSettings['binarydata_handler'] ) )
        {
            $contextualizer->setContextualParameter( 'io.binarydata_handler', $currentScope, $scopeSettings['binarydata_handler'] );
        }
    }
}
