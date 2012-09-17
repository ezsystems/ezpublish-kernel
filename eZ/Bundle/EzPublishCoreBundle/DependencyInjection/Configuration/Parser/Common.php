<?php
/**
 * File containing the Common class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser,
    Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration parser handling all basic configuration (aka "common")
 */
class Common implements Parser
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
            ->arrayNode( 'languages' )
                ->info( 'Available languages, in order of precedence' )
                ->example( array( 'fre-FR', 'eng-GB' ) )
                ->prototype( 'scalar' )->end()
            ->end()
            ->arrayNode( 'database' )
                ->children()
                    ->enumNode( 'type' )->values( array( 'mysql', 'pgsql', 'sqlite' ) )->info( 'The database driver. Can be mysql, pgsql or sqlite.' )->end()
                    ->scalarNode( 'server' )->defaultValue( 'localhost' )->end()
                    ->scalarNode( 'port' )->end()
                    ->scalarNode( 'user' )->cannotBeEmpty()->end()
                    ->scalarNode( 'password' )->end()
                    ->scalarNode( 'database_name' )->cannotBeEmpty()->end()
                    ->scalarNode( 'dsn' )->info( 'Full database DSN. Will replace settings above.' )->example( 'mysql://root:root@localhost:3306/ezdemo' )->end()
                ->end()
            ->end()
            ->booleanNode( 'url_alias_router' )
                ->info( 'Whether to use UrlAliasRouter or not. If false, will let the legacy kernel handle url aliases.' )
                ->defaultValue( true )
            ->end()
        ;
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        foreach ( $config['system'] as $sa => $settings )
        {
            if ( isset( $settings['languages'] ) )
            {
                $container->setParameter( "ezsettings.$sa.languages", $settings['languages'] );
            }

            if ( isset( $settings['database'] ) )
            {
                if ( isset( $settings['database']['dsn'] ) )
                {
                    $dsn = $settings['database']['dsn'];
                }
                else
                {
                    $port = '';
                    if ( isset( $settings['database']['port'] ) && !empty( $settings['database']['port'] ) )
                        $port = ":{$settings['database']['port']}";

                    $dsn = "{$settings['database']['type']}://{$settings['database']['user']}:{$settings['database']['password']}@{$settings['database']['server']}$port/{$settings['database']['database_name']}";
                    $container->setParameter( "ezsettings.$sa.database.dsn", $dsn );
                }
            }
        }
    }
}
