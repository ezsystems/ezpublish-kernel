<?php
/**
 * File containing the Templates class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Templates extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     *
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( static::NODE_KEY )
                ->info( static::INFO )
                ->prototype( 'array' )
                    ->children()
                        ->scalarNode( 'template' )
                            ->info( static::INFO_TEMPLATE_KEY )
                            ->isRequired()
                        ->end()
                        ->scalarNode( 'priority' )
                            ->defaultValue( 0 )
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        foreach ( $config['siteaccess']['groups'] as $group => $saArray )
        {
            if ( isset( $config[$this->baseKey][$group][static::NODE_KEY] ) )
            {
                $container->setParameter(
                    "ezsettings.$group." . static::NODE_KEY,
                    $config[$this->baseKey][$group][static::NODE_KEY]
                );
            }
        };
        $this->registerInternalConfigArray(
            static::NODE_KEY, $config, $container
        );
    }
}
