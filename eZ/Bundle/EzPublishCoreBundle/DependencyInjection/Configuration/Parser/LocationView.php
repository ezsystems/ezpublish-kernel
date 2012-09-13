<?php
/**
 * File containing the LocationView class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser,
    Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\DependencyInjection\ContainerBuilder;

class LocationView implements Parser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'location_view' )
                ->info( 'Template selection settings when displaying a location' )
                ->useAttributeAsKey( 'key' )
                ->prototype( 'array' )
                ->useAttributeAsKey( 'key' )
                    ->prototype( 'array' )
                        ->children()
                            ->scalarNode( 'template' )->isRequired()->info( 'Your template path, as MyBundle:subdir:my_template.html.twig' )->end()
                            ->arrayNode( 'match' )
                                ->info( 'Condition matchers configuration' )
                                ->useAttributeAsKey( 'key' )
                                ->prototype( 'variable' )->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     * @param array $siteAccessGroupDefinition
     * @return mixed
     */
    public function registerInternalConfig( ContainerBuilder $container, array $config, array $siteAccessGroupDefinition )
    {
        // TODO: Implement registerInternalConfig() method.
    }
}
