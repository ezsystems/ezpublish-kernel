<?php

/**
 * File containing the EzPublishCoreExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorAwareInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter\YamlSuggestionFormatter;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\PoliciesConfigBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\PolicyProviderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface;
use RuntimeException;

class EzPublishCoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector
     */
    private $suggestionCollector;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface
     */
    private $mainConfigParser;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface[]
     */
    private $configParsers;

    /**
     * @var PolicyProviderInterface[]
     */
    private $policyProviders = [];

    /**
     * Holds a collection of YAML files, as an array with directory path as a
     * key to the array of contained file names.
     *
     * @var array
     */
    private $defaultSettingsCollection = [];

    public function __construct(array $configParsers = array())
    {
        $this->configParsers = $configParsers;
        $this->suggestionCollector = new SuggestionCollector();
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
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $configuration = $this->getConfiguration($configs, $container);

        // Note: this is where the transformation occurs
        $config = $this->processConfiguration($configuration, $configs);

        // Base services and services overrides
        $loader->load('services.yml');
        // Security services
        $loader->load('security.yml');
        // Slots
        $loader->load('slot.yml');

        // Default settings
        $this->handleDefaultSettingsLoading($container, $loader);

        $this->registerRepositoriesConfiguration($config, $container);
        $this->registerSiteAccessConfiguration($config, $container);
        $this->registerImageMagickConfiguration($config, $container);
        $this->registerPageConfiguration($config, $container);

        // Routing
        $this->handleRouting($config, $container, $loader);
        // Public API loading
        $this->handleApiLoading($container, $loader);
        $this->handleTemplating($container, $loader);
        $this->handleSessionLoading($container, $loader);
        $this->handleCache($config, $container, $loader);
        $this->handleLocale($config, $container, $loader);
        $this->handleHelpers($config, $container, $loader);
        $this->handleImage($config, $container, $loader);

        // Map settings
        $processor = new ConfigurationProcessor($container, 'ezsettings');
        $processor->mapConfig($config, $this->getMainConfigParser());

        if ($this->suggestionCollector->hasSuggestions()) {
            $message = '';
            $suggestionFormatter = new YamlSuggestionFormatter();
            foreach ($this->suggestionCollector->getSuggestions() as $suggestion) {
                $message .= $suggestionFormatter->format($suggestion) . "\n\n";
            }

            throw new InvalidArgumentException($message);
        }

        $this->handleSiteAccessesRelation($container);
        $this->buildPolicyMap($container);
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getMainConfigParser(), $this->suggestionCollector);
    }

    /**
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface
     */
    private function getMainConfigParser()
    {
        if ($this->mainConfigParser === null) {
            foreach ($this->configParsers as $parser) {
                if ($parser instanceof SuggestionCollectorAwareInterface) {
                    $parser->setSuggestionCollector($this->suggestionCollector);
                }
            }

            $this->mainConfigParser = new ConfigParser($this->configParsers);
        }

        return $this->mainConfigParser;
    }

    /**
     * Handle default settings.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleDefaultSettingsLoading(ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('default_settings.yml');

        foreach ($this->defaultSettingsCollection as $fileLocation => $files) {
            $externalLoader = new Loader\YamlFileLoader($container, new FileLocator($fileLocation));
            foreach ($files as $file) {
                $externalLoader->load($file);
            }
        }
    }

    private function registerRepositoriesConfiguration(array $config, ContainerBuilder $container)
    {
        if (!isset($config['repositories'])) {
            $config['repositories'] = array();
        }

        $container->setParameter('ezpublish.repositories', $config['repositories']);
    }

    private function registerSiteAccessConfiguration(array $config, ContainerBuilder $container)
    {
        if (!isset($config['siteaccess'])) {
            $config['siteaccess'] = array();
            $config['siteaccess']['list'] = array('setup');
            $config['siteaccess']['default_siteaccess'] = 'setup';
            $config['siteaccess']['groups'] = array();
            $config['siteaccess']['match'] = null;
        }

        $container->setParameter('ezpublish.siteaccess.list', $config['siteaccess']['list']);
        ConfigurationProcessor::setAvailableSiteAccesses($config['siteaccess']['list']);
        $container->setParameter('ezpublish.siteaccess.default', $config['siteaccess']['default_siteaccess']);
        $container->setParameter('ezpublish.siteaccess.match_config', $config['siteaccess']['match']);

        // Register siteaccess groups + reverse
        $container->setParameter('ezpublish.siteaccess.groups', $config['siteaccess']['groups']);
        $groupsBySiteaccess = array();
        foreach ($config['siteaccess']['groups'] as $groupName => $groupMembers) {
            foreach ($groupMembers as $member) {
                if (!isset($groupsBySiteaccess[$member])) {
                    $groupsBySiteaccess[$member] = array();
                }

                $groupsBySiteaccess[$member][] = $groupName;
            }
        }
        $container->setParameter('ezpublish.siteaccess.groups_by_siteaccess', $groupsBySiteaccess);
        ConfigurationProcessor::setGroupsBySiteAccess($groupsBySiteaccess);
    }

    private function registerImageMagickConfiguration(array $config, ContainerBuilder $container)
    {
        if (isset($config['imagemagick'])) {
            $container->setParameter('ezpublish.image.imagemagick.enabled', $config['imagemagick']['enabled']);
            if ($config['imagemagick']['enabled']) {
                $container->setParameter('ezpublish.image.imagemagick.executable_path', dirname($config['imagemagick']['path']));
                $container->setParameter('ezpublish.image.imagemagick.executable', basename($config['imagemagick']['path']));
            }
        }

        $filters = isset($config['imagemagick']['filters']) ? $config['imagemagick']['filters'] : array();
        $filters = $filters + $container->getParameter('ezpublish.image.imagemagick.filters');
        $container->setParameter('ezpublish.image.imagemagick.filters', $filters);
    }

    private function registerPageConfiguration(array $config, ContainerBuilder $container)
    {
        if (isset($config['ezpage']['layouts'])) {
            $container->setParameter(
                'ezpublish.ezpage.layouts',
                $config['ezpage']['layouts'] + $container->getParameter('ezpublish.ezpage.layouts')
            );
        }
        if (isset($config['ezpage']['blocks'])) {
            $container->setParameter(
                'ezpublish.ezpage.blocks',
                $config['ezpage']['blocks'] + $container->getParameter('ezpublish.ezpage.blocks')
            );
        }
        if (isset($config['ezpage']['enabledLayouts'])) {
            $container->setParameter(
                'ezpublish.ezpage.enabledLayouts',
                $config['ezpage']['enabledLayouts'] + $container->getParameter('ezpublish.ezpage.enabledLayouts')
            );
        }
        if (isset($config['ezpage']['enabledBlocks'])) {
            $container->setParameter(
                'ezpublish.ezpage.enabledBlocks',
                $config['ezpage']['enabledBlocks'] + $container->getParameter('ezpublish.ezpage.enabledBlocks')
            );
        }
    }

    /**
     * Handle routing parameters.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleRouting(array $config, ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('routing.yml');
        $container->setAlias('router', 'ezpublish.chain_router');

        if (isset($config['router']['default_router']['non_siteaccess_aware_routes'])) {
            $container->setParameter(
                'ezpublish.default_router.non_siteaccess_aware_routes',
                array_merge(
                    $container->getParameter('ezpublish.default_router.non_siteaccess_aware_routes'),
                    $config['router']['default_router']['non_siteaccess_aware_routes']
                )
            );
        }
    }

    /**
     * Handle public API loading.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleApiLoading(ContainerBuilder $container, FileLoader $loader)
    {
        // Loading configuration from Core/settings
        $coreLoader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../../Publish/Core/settings')
        );
        $coreLoader->load('repository.yml');
        $coreLoader->load('fieldtype_external_storages.yml');
        $coreLoader->load('fieldtypes.yml');
        $coreLoader->load('indexable_fieldtypes.yml');
        $coreLoader->load('roles.yml');
        $coreLoader->load('storage_engines/common.yml');
        $coreLoader->load('storage_engines/cache.yml');
        $coreLoader->load('storage_engines/legacy.yml');
        $coreLoader->load('storage_engines/shortcuts.yml');
        $coreLoader->load('search_engines/common.yml');
        $coreLoader->load('utils.yml');
        $coreLoader->load('io.yml');

        // Public API services
        $loader->load('papi.yml');

        // Built-in field types
        $loader->load('fieldtype_services.yml');

        // Storage engine
        $loader->load('storage_engines.yml');
    }

    /**
     * Handle templating parameters.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleTemplating(ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('templating.yml');
    }

    /**
     * Handle session parameters.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleSessionLoading(ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('session.yml');
    }

    /**
     * Handle cache parameters.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     *
     * @throws \InvalidArgumentException
     */
    private function handleCache(array $config, ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('cache.yml');

        if (isset($config['http_cache']['purge_type'])) {
            switch ($config['http_cache']['purge_type']) {
                case 'local':
                    $purgeService = 'ezpublish.http_cache.purge_client.local';
                    break;
                case 'http':
                    $purgeService = 'ezpublish.http_cache.purge_client.fos';
                    break;
                default:
                    if (!$container->has($config['http_cache']['purge_type'])) {
                        throw new \InvalidArgumentException("Invalid ezpublish.http_cache.purge_type. Can be 'single', 'multiple' or a valid service identifier implementing PurgeClientInterface.");
                    }

                    $purgeService = $config['http_cache']['purge_type'];
            }

            $container->setAlias('ezpublish.http_cache.purge_client', $purgeService);
        }
    }

    /**
     * Handle locale parameters.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleLocale(array $config, ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('locale.yml');
        $container->setParameter(
            'ezpublish.locale.conversion_map',
            $config['locale_conversion'] + $container->getParameter('ezpublish.locale.conversion_map')
        );
    }

    /**
     * Handle helpers.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleHelpers(array $config, ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('helpers.yml');
    }

    /**
     * Handles relation between SiteAccesses.
     * Related SiteAccesses share the same repository and root location id.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function handleSiteAccessesRelation(ContainerBuilder $container)
    {
        $configResolver = $container->get('ezpublish.config.resolver.core');
        $configResolver->setContainer($container);

        $saRelationMap = array();
        $saList = $container->getParameter('ezpublish.siteaccess.list');
        // First build the SiteAccess relation map, indexed by repository and rootLocationId.
        foreach ($saList as $sa) {
            $repository = $configResolver->getParameter('repository', 'ezsettings', $sa);
            if (!isset($saRelationMap[$repository])) {
                $saRelationMap[$repository] = array();
            }

            $rootLocationId = $configResolver->getParameter('content.tree_root.location_id', 'ezsettings', $sa);
            if (!isset($saRelationMap[$repository][$rootLocationId])) {
                $saRelationMap[$repository][$rootLocationId] = array();
            }
            $saRelationMap[$repository][$rootLocationId][] = $sa;
        }
        $container->setParameter('ezpublish.siteaccess.relation_map', $saRelationMap);

        // Now build the related SiteAccesses list, based on the relation map.
        foreach ($saList as $sa) {
            $repository = $configResolver->getParameter('repository', 'ezsettings', $sa);
            $rootLocationId = $configResolver->getParameter('content.tree_root.location_id', 'ezsettings', $sa);
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
    private function handleImage(array $config, ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('image.yml');
    }

    private function buildPolicyMap(ContainerBuilder $container)
    {
        $policiesBuilder = new PoliciesConfigBuilder($container);
        foreach ($this->policyProviders as $provider) {
            $provider->addPolicies($policiesBuilder);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        // Default settings for FOSHttpCacheBundle
        $configFile = __DIR__ . '/../Resources/config/fos_http_cache.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('fos_http_cache', $config);
        $container->addResource(new FileResource($configFile));
    }

    /**
     * Adds a new policy provider to the internal collection.
     * One can call this method from a bundle `build()` method.
     *
     * ```php
     * public function build(ContainerBuilder $container)
     * {
     *     $ezExtension = $container->getExtension('ezpublish');
     *     $ezExtension->addPolicyProvider($myPolicyProvider);
     * }
     * ```
     *
     * @since 6.0
     *
     * @param PolicyProviderInterface $policyProvider
     */
    public function addPolicyProvider(PolicyProviderInterface $policyProvider)
    {
        $this->policyProviders[] = $policyProvider;
    }

    /**
     * Adds a new config parser to the internal collection.
     * One can call this method from a bundle `build()` method.
     *
     * ```php
     * public function build(ContainerBuilder $container)
     * {
     *     $ezExtension = $container->getExtension('ezpublish');
     *     $ezExtension->addConfigParser($myConfigParser);
     * }
     * ```
     *
     * @since 6.0
     *
     * @param \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface $configParser
     */
    public function addConfigParser(ParserInterface $configParser)
    {
        if ($this->mainConfigParser !== null) {
            throw new RuntimeException('Main config parser is already instantiated');
        }

        $this->configParsers[] = $configParser;
    }

    /**
     * Adds new default settings to the internal collection.
     * One can call this method from a bundle `build()` method.
     *
     * ```php
     * public function build(ContainerBuilder $container)
     * {
     *     $ezExtension = $container->getExtension('ezpublish');
     *     $ezExtension->addDefaultSettings(
     *         __DIR__ . '/Resources/config',
     *         ['default_settings.yml']
     *     );
     * }
     * ```
     *
     * @since 6.0
     *
     * @param string $fileLocation
     * @param array $files
     */
    public function addDefaultSettings($fileLocation, array $files)
    {
        $this->defaultSettingsCollection[$fileLocation] = $files;
    }
}
