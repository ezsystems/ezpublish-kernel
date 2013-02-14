<?php
/**
 * File containing the Content class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration parser handling content related config
 */
class Content implements Parser
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
            ->arrayNode( 'content' )
                ->info( 'Content related configuration' )
                ->children()
                    ->booleanNode( 'view_cache' )->defaultValue( true )->end()
                    ->booleanNode( 'ttl_cache' )->defaultValue( false )->end()
                    ->scalarNode( 'default_ttl' )->info( 'Default value for TTL cache, in seconds' )->defaultValue( 60 )->end()
                ->end()
            ->end();
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return mixed
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        foreach ( $config['system'] as $sa => $settings )
        {
            if ( !empty( $settings['content'] ) )
            {
                $container->setParameter( "ezsettings.$sa.content.view_cache", $settings['content']['view_cache'] );
                $container->setParameter( "ezsettings.$sa.content.ttl_cache", $settings['content']['ttl_cache'] );
                $container->setParameter( "ezsettings.$sa.content.default_ttl", $settings['content']['default_ttl'] );
            }
        }
    }
}
