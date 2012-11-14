<?php
/**
 * File containing the EzPublishCoreExtension class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader,
    Symfony\Component\DependencyInjection\Loader\FileLoader,
    Symfony\Component\Config\FileLocator;

class EzPublishCoreExtension extends Extension
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser[]
     */
    private $configParsers;

    public function __construct( array $configParsers = array() )
    {
        $this->configParsers = $configParsers;
    }

    public function getAlias()
    {
        return 'ezpublish';
    }

    /**
     * Loads a specific configuration.
     *
     * @param array            $configs    An array of configuration values
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../Resources/config' )
        );
        $configuration = $this->getConfiguration( $configs, $container );
        $config = $this->processConfiguration( $configuration, $configs );

        // Base services and services overrides
        $loader->load( 'services.yml' );
        // Security services
        $loader->load( 'security.yml' );
        // Default settings
        $loader->load( 'default_settings.yml' );
        $this->registerSiteAcccessConfiguration( $config, $container );
        $this->registerImageMagickConfiguration( $config, $container );

        // Routing
        $this->handleRouting( $container, $loader );
        // Public API loading
        $this->handleApiLoading( $container, $loader );
        $this->handleTemplating( $container, $loader );
        $this->handleSessionLoading( $container, $loader );
        $this->handleCache( $config, $container, $loader );

        // Map settings
        foreach ( $this->configParsers as $configParser )
        {
            $configParser->registerInternalConfig( $config, $container );
        }
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration
     */
    public function getConfiguration( array $config, ContainerBuilder $container )
    {
        return new Configuration( $this->configParsers );
    }

    private function registerSiteAcccessConfiguration( array $config, ContainerBuilder $container )
    {
        if ( !isset( $config['siteaccess'] ) )
        {
            $config['siteaccess'] = array();
            $config['siteaccess']['list'] = array( 'setup');
            $config['siteaccess']['default_siteaccess'] = 'setup';
            $config['siteaccess']['groups'] = array();
            $config['siteaccess']['match'] = null;
        }

        $container->setParameter( 'ezpublish.siteaccess.list', $config['siteaccess']['list'] );
        $container->setParameter( 'ezpublish.siteaccess.default', $config['siteaccess']['default_siteaccess'] );
        $container->setParameter( 'ezpublish.siteaccess.match_config', $config['siteaccess']['match'] );

        // Register siteaccess groups + reverse
        $container->setParameter( 'ezpublish.siteaccess.groups', $config['siteaccess']['groups'] );
        $groupsBySiteaccess = array();
        foreach ( $config['siteaccess']['groups'] as $groupName => $groupMembers )
        {
            foreach ( $groupMembers as $member )
            {
                if ( !isset( $groupsBySiteaccess[$member] ) )
                    $groupsBySiteaccess[$member] = array();

                $groupsBySiteaccess[$member][] = $groupName;
            }
        }
        $container->setParameter( 'ezpublish.siteaccess.groups_by_siteaccess', $groupsBySiteaccess );
    }

    private function registerImageMagickConfiguration( array $config, ContainerBuilder $container )
    {
        if ( isset( $config['imagemagick'] ) )
        {
            $container->setParameter( 'ezpublish.image.imagemagick.enabled', $config['imagemagick']['enabled'] );
            if ( $config['imagemagick']['enabled'] )
            {
                $container->setParameter( 'ezpublish.image.imagemagick.executable_path', dirname( $config['imagemagick']['path'] ) );
                $container->setParameter( 'ezpublish.image.imagemagick.executable', basename( $config['imagemagick']['path'] ) );
            }
        }

        $filters = isset( $config['imagemagick']['filters'] ) ? $config['imagemagick']['filters'] : array();
        $filters = $filters + $container->getParameter( 'ezpublish.image.imagemagick.filters' );
        $container->setParameter( 'ezpublish.image.imagemagick.filters', $filters );
    }

    /**
     * Handle routing parameters
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleRouting( ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'routing.yml' );
        $container->setAlias( 'router', 'ezpublish.chain_router' );
    }

    /**
     * Handle public API loading
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleApiLoading( ContainerBuilder $container, FileLoader $loader )
    {
        // Public API services
        $loader->load( 'papi.yml' );
        // Built-in field types
        $loader->load( 'fieldtypes.yml' );
        // Built-in storage engines
        $loader->load( 'storage_engines.yml' );
        // Roles and limitations
        $loader->load( 'roles.yml' );
    }

    /**
     * Handle templating parameters
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleTemplating( ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'templating.yml' );
    }

    /**
     * Handle session parameters
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleSessionLoading( ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'session.yml' );
    }

    /**
     * Handle cache parameters
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private function handleCache( array $config, ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'cache.yml' );
        if ( isset( $config['http_cache']['purge_type'] ) )
        {
            switch ( $config['http_cache']['purge_type'] )
            {
                case 'local':
                    $purgeService = 'ezpublish.http_cache.purge_client.local';
                    break;
                case 'single_http':
                    $purgeService = 'ezpublish.http_cache.purge_client.single_request';
                    break;
                case 'multiple_http':
                    $purgeService = 'ezpublish.http_cache.purge_client.multi_request';
                    break;
                default:
                    if ( !$container->has( $config['http_cache']['purge_type'] ) )
                        throw new \InvalidArgumentException( "Invalid ezpublish.http_cache.purge_type. Can be 'single', 'multiple' or a valid service identifier implementing PurgeClientInterface." );

                    $purgeService = $config['http_cache']['purge_type'];
            }

            $container->setAlias( 'ezpublish.http_cache.purge_client', $purgeService );
        }

        if ( isset( $config['http_cache']['timeout'] ) )
        {
            $container->setParameter( 'ezpublish.http_cache.purge_client.http_client.timeout', (int)$config['http_cache']['timeout'] );
        }
    }
}
