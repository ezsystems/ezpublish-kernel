<?php
/**
 * File containing the EzPublishCoreExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorAwareInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter\YamlSuggestionFormatter;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

class EzPublishCoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector
     */
    private $suggestionCollector;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface
     */
    private $configParser;

    public function __construct( array $configParsers = array() )
    {
        $this->suggestionCollector = new SuggestionCollector();
        foreach ( $configParsers as $parser )
        {
            if ( $parser instanceof SuggestionCollectorAwareInterface )
            {
                $parser->setSuggestionCollector( $this->suggestionCollector );
            }
        }
        $this->configParser = new ConfigParser( $configParsers );
    }

    public function getAlias()
    {
        return 'ezpublish';
    }

    /**
     * Loads a specific configuration.
     *
     * @param mixed[] $configs An array of configuration values
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

        // Note: this is where the transformation occurs
        $config = $this->processConfiguration( $configuration, $configs );

        // Base services and services overrides
        $loader->load( 'services.yml' );
        // Security services
        $loader->load( 'security.yml' );
        // Default settings
        $loader->load( 'default_settings.yml' );
        $this->registerRepositoriesConfiguration( $config, $container );
        $this->registerSiteAccessConfiguration( $config, $container );
        $this->registerImageMagickConfiguration( $config, $container );
        $this->registerPageConfiguration( $config, $container );

        // Routing
        $this->handleRouting( $config, $container, $loader );
        // Public API loading
        $this->handleApiLoading( $container, $loader );
        $this->handleTemplating( $container, $loader );
        $this->handleSessionLoading( $container, $loader );
        $this->handleCache( $config, $container, $loader );
        $this->handleLocale( $config, $container, $loader );
        $this->handleHelpers( $config, $container, $loader );
        $this->handleImage( $config, $container, $loader );

        // Map settings
        $processor = new ConfigurationProcessor( $container, 'ezsettings' );
        $processor->mapConfig( $config, $this->configParser );

        if ( $this->suggestionCollector->hasSuggestions() )
        {
            $message = '';
            $suggestionFormatter = new YamlSuggestionFormatter();
            foreach ( $this->suggestionCollector->getSuggestions() as $suggestion )
            {
                $message .= $suggestionFormatter->format( $suggestion ) . "\n\n";
            }

            throw new InvalidArgumentException( $message );
        }

        $this->handleSiteAccessesRelation( $container );
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration
     */
    public function getConfiguration( array $config, ContainerBuilder $container )
    {
        return new Configuration( $this->configParser, $this->suggestionCollector );
    }

    private function registerRepositoriesConfiguration( array $config, ContainerBuilder $container )
    {
        if ( !isset( $config['repositories'] ) )
        {
            $config['repositories'] = array();
        }

        $container->setParameter( 'ezpublish.repositories', $config['repositories'] );
    }

    private function registerSiteAccessConfiguration( array $config, ContainerBuilder $container )
    {
        if ( !isset( $config['siteaccess'] ) )
        {
            $config['siteaccess'] = array();
            $config['siteaccess']['list'] = array( 'setup' );
            $config['siteaccess']['default_siteaccess'] = 'setup';
            $config['siteaccess']['groups'] = array();
            $config['siteaccess']['match'] = null;
        }

        $container->setParameter( 'ezpublish.siteaccess.list', $config['siteaccess']['list'] );
        ConfigurationProcessor::setAvailableSiteAccesses( $config['siteaccess']['list'] );
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
        ConfigurationProcessor::setGroupsBySiteAccess( $groupsBySiteaccess );
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

    private function registerPageConfiguration( array $config, ContainerBuilder $container )
    {
        if ( isset( $config['ezpage']['layouts'] ) )
        {
            $container->setParameter(
                'ezpublish.ezpage.layouts',
                $config['ezpage']['layouts'] + $container->getParameter( 'ezpublish.ezpage.layouts' )
            );
        }
        if ( isset( $config['ezpage']['blocks'] ) )
        {
            $container->setParameter(
                'ezpublish.ezpage.blocks',
                $config['ezpage']['blocks'] + $container->getParameter( 'ezpublish.ezpage.blocks' )
            );
        }
        if ( isset( $config['ezpage']['enabledLayouts'] ) )
        {
            $container->setParameter(
                'ezpublish.ezpage.enabledLayouts',
                $config['ezpage']['enabledLayouts'] + $container->getParameter( 'ezpublish.ezpage.enabledLayouts' )
            );
        }
        if ( isset( $config['ezpage']['enabledBlocks'] ) )
        {
            $container->setParameter(
                'ezpublish.ezpage.enabledBlocks',
                $config['ezpage']['enabledBlocks'] + $container->getParameter( 'ezpublish.ezpage.enabledBlocks' )
            );
        }

    }

    /**
     * Handle routing parameters
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleRouting( array $config, ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'routing.yml' );
        $container->setAlias( 'router', 'ezpublish.chain_router' );

        if ( isset( $config['router']['default_router']['non_siteaccess_aware_routes'] ) )
        {
            $container->setParameter(
                'ezpublish.default_router.non_siteaccess_aware_routes',
                array_merge(
                    $container->getParameter( 'ezpublish.default_router.non_siteaccess_aware_routes' ),
                    $config['router']['default_router']['non_siteaccess_aware_routes']
                )
            );
        }

        // Define additional routes that are allowed with legacy_mode: true.
        if ( isset( $config['router']['default_router']['legacy_aware_routes'] ) )
        {
            $container->setParameter(
                'ezpublish.default_router.legacy_aware_routes',
                array_merge(
                    $container->getParameter( 'ezpublish.default_router.legacy_aware_routes' ),
                    $config['router']['default_router']['legacy_aware_routes']
                )
            );
        }
    }

    /**
     * Handle public API loading
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleApiLoading( ContainerBuilder $container, FileLoader $loader )
    {
        // Loading configuration from Core/settings
        $coreLoader = new Loader\YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../../../Publish/Core/settings' )
        );
        $coreLoader->load( 'repository.yml' );
        $coreLoader->load( 'fieldtype_external_storages.yml' );
        $coreLoader->load( 'fieldtypes.yml' );
        $coreLoader->load( 'roles.yml' );
        $coreLoader->load( 'storage_engines/common.yml' );
        $coreLoader->load( 'storage_engines/cache.yml' );
        $coreLoader->load( 'storage_engines/legacy.yml' );
        $coreLoader->load( 'utils.yml' );
        $coreLoader->load( 'io.yml' );

        // Public API services
        $loader->load( 'papi.yml' );

        // Built-in field types
        $loader->load( 'fieldtype_services.yml' );

        // Storage engine
        $loader->load( 'storage_engines.yml' );
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
                    {
                        throw new \InvalidArgumentException( "Invalid ezpublish.http_cache.purge_type. Can be 'single', 'multiple' or a valid service identifier implementing PurgeClientInterface." );
                    }

                    $purgeService = $config['http_cache']['purge_type'];
            }

            $container->setAlias( 'ezpublish.http_cache.purge_client', $purgeService );
        }

        if ( isset( $config['http_cache']['timeout'] ) )
        {
            $container->setParameter( 'ezpublish.http_cache.purge_client.http_client.timeout', (int)$config['http_cache']['timeout'] );
        }
    }

    /**
     * Handle locale parameters.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleLocale( array $config, ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'locale.yml' );
        $container->setParameter(
            'ezpublish.locale.conversion_map',
            $config['locale_conversion'] + $container->getParameter( 'ezpublish.locale.conversion_map' )
        );
    }

    /**
     * Handle helpers.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleHelpers( array $config, ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'helpers.yml' );
    }

    /**
     * Handles relation between SiteAccesses.
     * Related SiteAccesses share the same repository and root location id.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function handleSiteAccessesRelation( ContainerBuilder $container )
    {
        $configResolver = $container->get( 'ezpublish.config.resolver.core' );
        $configResolver->setContainer( $container );

        $saRelationMap = array();
        $saList = $container->getParameter( 'ezpublish.siteaccess.list' );
        // First build the SiteAccess relation map, indexed by repository and rootLocationId.
        foreach ( $saList as $sa )
        {
            // Exclude siteaccesses in legacy_mode (e.g. admin interface)
            if ( $configResolver->getParameter( 'legacy_mode', 'ezsettings', $sa ) === true )
            {
                continue;
            }

            $repository = $configResolver->getParameter( 'repository', 'ezsettings', $sa );
            if ( !isset( $saRelationMap[$repository] ) )
            {
                $saRelationMap[$repository] = array();
            }

            $rootLocationId = $configResolver->getParameter( 'content.tree_root.location_id', 'ezsettings', $sa );
            if ( !isset( $saRelationMap[$repository][$rootLocationId] ) )
            {
                $saRelationMap[$repository][$rootLocationId] = array();
            }
            $saRelationMap[$repository][$rootLocationId][] = $sa;
        }
        $container->setParameter( 'ezpublish.siteaccess.relation_map', $saRelationMap );

        // Now build the related SiteAccesses list, based on the relation map.
        foreach ( $saList as $sa )
        {
            if ( $configResolver->getParameter( 'legacy_mode', 'ezsettings', $sa ) === true )
            {
                continue;
            }

            $repository = $configResolver->getParameter( 'repository', 'ezsettings', $sa );
            $rootLocationId = $configResolver->getParameter( 'content.tree_root.location_id', 'ezsettings', $sa );
            $container->setParameter(
                "ezsettings.$sa.related_siteaccesses",
                $saRelationMap[$repository][$rootLocationId]
            );
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @param FileLoader $loader
     */
    private function handleImage( array $config, ContainerBuilder $container, FileLoader $loader )
    {
        $loader->load( 'image.yml' );
    }

    public function prepend( ContainerBuilder $container )
    {
        // Default settings for FOSHttpCacheBundle
        $configFile = __DIR__ . '/../Resources/config/fos_http_cache.yml';
        $config = Yaml::parse( file_get_contents( $configFile ) );
        $container->prependExtensionConfig( 'fos_http_cache', $config );
        $container->addResource( new FileResource( $configFile ) );
    }
}
