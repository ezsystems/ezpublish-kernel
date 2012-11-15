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
                ->cannotBeEmpty()
                ->info( 'Available languages, in order of precedence' )
                ->example( array( 'fre-FR', 'eng-GB' ) )
                ->prototype( 'scalar' )->end()
            ->end()
            ->arrayNode( 'database' )
                ->children()
                    ->enumNode( 'type' )->values( array( 'mysql', 'pgsql', 'sqlite' ) )->info( 'The database driver. Can be mysql, pgsql or sqlite.' )->end()
                    ->scalarNode( 'server' )->end()
                    ->scalarNode( 'port' )->end()
                    ->scalarNode( 'user' )->cannotBeEmpty()->end()
                    ->scalarNode( 'password' )->end()
                    ->scalarNode( 'database_name' )->cannotBeEmpty()->end()
                    ->scalarNode( 'charset' )->defaultValue( 'utf8' )->end()
                    ->scalarNode( 'socket' )->end()
                    ->arrayNode( 'options' )
                        ->info( 'Arbitrary options, supported by your DB driver ("driver-opts" in PDO)' )
                        ->example( array( 'foo' => 'bar', 'someOptionName' => array( 'one', 'two', 'three' ) ) )
                        ->useAttributeAsKey( 'key' )
                        ->prototype( 'variable' )->end()
                    ->end()
                    ->scalarNode( 'dsn' )->info( 'Full database DSN. Will replace settings above.' )->example( 'mysql://root:root@localhost:3306/ezdemo' )->end()
                ->end()
            ->end()
            ->scalarNode( 'var_dir' )
                ->cannotBeEmpty()
                ->example( 'var/ezdemo_site' )
                ->info( 'The directory relative to web/ where files are stored. Default value is "var"' )
            ->end()
            ->scalarNode( 'storage_dir' )
                ->cannotBeEmpty()
                ->info( "Directory where to place new files for storage, it's relative to var directory. Default value is 'storage'" )
            ->end()
            ->scalarNode( 'binary_dir' )
                ->cannotBeEmpty()
                ->info( 'Directory where binary files (from ezbinaryfile field type) are stored. Default value is "original"' )
            ->end()
            ->booleanNode( 'legacy_mode' )
                ->info( 'Whether to use legacy mode or not. If true, will let the legacy kernel handle url aliases.' )
                ->defaultValue( false )
            ->end()
            ->scalarNode( 'session_name' )
                ->info( 'The session name. If you want a session name per siteaccess, use "{siteaccess_hash}" token. Will override default session name from framework.session.name' )
                ->example( array( 'session_name' => 'eZSESSID{siteaccess_hash}' ) )
            ->end()
            ->arrayNode( 'http_cache' )
                ->info( 'Settings related to Http cache' )
                ->cannotBeEmpty()
                ->children()
                    ->arrayNode( 'purge_servers' )
                        ->info( 'Servers to use for Http PURGE (will NOT be used if ezpublish.http_cache.purge_type is "local").' )
                        ->example( array( 'http://localhost/', 'http://another.server/' ) )
                        ->requiresAtLeastOneElement()
                        ->prototype( 'scalar' )->end()
                    ->end()
                ->end()
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
            if ( !empty( $database ) )
            {
                // DSN is prioritary to any other setting
                if ( isset( $database['dsn'] ) )
                {
                    $container->setParameter( "ezsettings.$sa.database.params", $database['dsn'] );
                }
                else
                {
                    // Renaming dbParams to parameters supported by ezcDb.
                    $database['database'] = $database['database_name'];
                    $database['host'] = $database['server'];
                    $database['driver-opts'] = $database['options'];
                    unset( $database['database_name'], $database['server'], $database['options'] );
                    $container->setParameter( "ezsettings.$sa.database.params", $database );
                }
            }
        }
        foreach ( $config[$this->baseKey] as $sa => $settings )
        {
            if ( isset( $settings['legacy_mode'] ) )
            {
                $container->setParameter( "ezsettings.$sa.legacy_mode", $settings['legacy_mode'] );
                $container->setParameter( "ezsettings.$sa.url_alias_router", !$settings['legacy_mode'] );
            }
            if ( isset( $settings['var_dir'] ) )
                $container->setParameter( "ezsettings.$sa.var_dir", $settings['var_dir'] );
            if ( isset( $settings['storage_dir'] ) )
                $container->setParameter( "ezsettings.$sa.storage_dir", $settings['storage_dir'] );
            if ( isset( $settings['binary_dir'] ) )
                $container->setParameter( "ezsettings.$sa.binary_dir", $settings['binary_dir'] );
            if ( isset( $settings['session_name'] ) )
                $container->setParameter( "ezsettings.$sa.session_name", $settings['session_name'] );
            if ( isset( $settings['http_cache']['purge_servers'] ) )
                $container->setParameter( "ezsettings.$sa.http_cache.purge_servers", $settings['http_cache']['purge_servers'] );
        }
    }
}
