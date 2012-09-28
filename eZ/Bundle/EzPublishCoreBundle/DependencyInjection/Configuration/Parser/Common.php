<?php
/**
 * File containing the Common class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser,
    Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration parser handling all basic configuration (aka "common")
 */
class Common extends AbstractParser
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
            ->scalarNode( 'var_dir' )
                ->cannotBeEmpty()
                ->defaultValue( 'var' )
                ->example( 'var/ezdemo_site' )
                ->info( 'The directory relative to web/ where files are stored' )
            ->end()
            ->scalarNode( 'storage_dir' )
                ->cannotBeEmpty()
                ->defaultValue( 'storage' )
            ->end()
            ->scalarNode( 'binary_dir' )
                ->cannotBeEmpty()
                ->defaultValue( 'original' )
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
        $this->registerInternalConfigArray(
            'languages', $config, $container, self::UNIQUE
        );
        $this->registerInternalConfigArray( 'database', $config, $container );
        foreach ( $config['siteaccess']['list'] as $sa )
        {
            $database = $container->getParameter( "ezsettings.$sa.database" );
            $port = '';
            if ( isset( $database['port'] ) && !empty( $database['port'] ) )
                $port = ":{$database['port']}";

            $dsn = "{$database['type']}://{$database['user']}:{$database['password']}@{$database['server']}$port/{$database['database_name']}";
            $container->setParameter( "ezsettings.$sa.database.dsn", $dsn );
        }
        foreach ( $config['system'] as $sa => $settings )
        {
            $container->setParameter( "ezsettings.$sa.url_alias_router", $settings['url_alias_router'] );
            $container->setParameter( "ezsettings.$sa.var_dir", $settings['var_dir'] );
            $storageDir = rtrim( $settings['var_dir'], '/' ) . '/' . $settings['storage_dir'];
            $container->setParameter( "ezsettings.$sa.storage_dir", $storageDir );
            $container->setParameter(
                "ezsettings.$sa.binary_dir",
                ltrim( $storageDir, '/' ) . '/' . $settings['binary_dir']
            );
        }
    }
}
