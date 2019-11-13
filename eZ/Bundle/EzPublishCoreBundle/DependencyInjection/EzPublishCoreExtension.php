<?php

/**
 * File containing the EzPublishCoreExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\QueryTypePass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorAwareInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter\YamlSuggestionFormatter;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\PoliciesConfigBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\PolicyProviderInterface;
use eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessConfigurationFilter;
use eZ\Publish\Core\QueryType\QueryType;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface;

class EzPublishCoreExtension extends Extension implements PrependExtensionInterface
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector */
    private $suggestionCollector;

    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface */
    private $mainConfigParser;

    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface[] */
    private $configParsers;

    /** @var PolicyProviderInterface[] */
    private $policyProviders = [];

    /**
     * Holds a collection of YAML files, as an array with directory path as a
     * key to the array of contained file names.
     *
     * @var array
     */
    private $defaultSettingsCollection = [];

    /** @var \eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessConfigurationFilter[] */
    private $siteaccessConfigurationFilters = [];

    public function __construct(array $configParsers = [])
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

        if (interface_exists('FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface')) {
            $loader->load('routing/js_routing.yml');
        }

        // Default settings
        $this->handleDefaultSettingsLoading($container, $loader);

        $this->registerRepositoriesConfiguration($config, $container);
        $this->registerSiteAccessConfiguration($config, $container);
        $this->registerImageMagickConfiguration($config, $container);
        $this->registerUrlAliasConfiguration($config, $container);
        $this->registerUrlWildcardsConfiguration($config, $container);

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
        $this->handleUrlChecker($config, $container, $loader);
        $this->handleUrlWildcards($config, $container, $loader);

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

        $this->buildPolicyMap($container);

        $container->registerForAutoconfiguration(QueryType::class)
            ->addTag(QueryTypePass::QUERY_TYPE_SERVICE_TAG);
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getMainConfigParser(), $this->suggestionCollector);
        $configuration->setSiteAccessConfigurationFilters($this->siteaccessConfigurationFilters);

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $this->prependTranslatorConfiguration($container);
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
     *
     * @throws \Exception
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
            $config['repositories'] = [];
        }

        foreach ($config['repositories'] as $name => &$repository) {
            if (empty($repository['fields_groups']['list'])) {
                $repository['fields_groups']['list'] = $container->getParameter('ezsettings.default.content.field_groups.list');
            }
        }

        $container->setParameter('ezpublish.repositories', $config['repositories']);
    }

    private function registerSiteAccessConfiguration(array $config, ContainerBuilder $container)
    {
        if (!isset($config['siteaccess'])) {
            $config['siteaccess'] = [];
            $config['siteaccess']['list'] = ['setup'];
            $config['siteaccess']['default_siteaccess'] = 'setup';
            $config['siteaccess']['groups'] = [];
            $config['siteaccess']['match'] = null;
        }

        $container->setParameter('ezpublish.siteaccess.list', $config['siteaccess']['list']);
        ConfigurationProcessor::setAvailableSiteAccesses($config['siteaccess']['list']);
        $container->setParameter('ezpublish.siteaccess.default', $config['siteaccess']['default_siteaccess']);
        $container->setParameter('ezpublish.siteaccess.match_config', $config['siteaccess']['match']);

        // Register siteaccess groups + reverse
        $container->setParameter('ezpublish.siteaccess.groups', $config['siteaccess']['groups']);
        $groupsBySiteaccess = [];
        foreach ($config['siteaccess']['groups'] as $groupName => $groupMembers) {
            foreach ($groupMembers as $member) {
                if (!isset($groupsBySiteaccess[$member])) {
                    $groupsBySiteaccess[$member] = [];
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

        $filters = isset($config['imagemagick']['filters']) ? $config['imagemagick']['filters'] : [];
        $filters = $filters + $container->getParameter('ezpublish.image.imagemagick.filters');
        $container->setParameter('ezpublish.image.imagemagick.filters', $filters);
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
        $container->getAlias('router')->setPublic(true);

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
        $coreLoader->load('repository/inner.yml');
        $coreLoader->load('repository/event.yml');
        $coreLoader->load('repository/siteaccessaware.yml');
        $coreLoader->load('repository/autowire.yml');
        $coreLoader->load('fieldtype_external_storages.yml');
        $coreLoader->load('fieldtypes.yml');
        $coreLoader->load('indexable_fieldtypes.yml');
        $coreLoader->load('fieldtype_services.yml');
        $coreLoader->load('roles.yml');
        $coreLoader->load('storage_engines/common.yml');
        $coreLoader->load('storage_engines/cache.yml');
        $coreLoader->load('storage_engines/legacy.yml');
        $coreLoader->load('storage_engines/shortcuts.yml');
        $coreLoader->load('search_engines/common.yml');
        $coreLoader->load('utils.yml');
        $coreLoader->load('io.yml');
        $coreLoader->load('policies.yml');
        $coreLoader->load('notification.yml');
        $coreLoader->load('user_preference.yml');
        $coreLoader->load('events.yml');
        $coreLoader->load('thumbnails.yml');

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

        $purgeService = null;
        if (isset($config['http_cache']['purge_type'])) {
            $container->setParameter('ezpublish.http_cache.purge_type', $config['http_cache']['purge_type']);
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
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleImage(array $config, ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('image.yml');

        $providers = [];
        if (isset($config['image_placeholder'])) {
            foreach ($config['image_placeholder'] as $name => $value) {
                if (isset($providers[$name])) {
                    throw new InvalidConfigurationException("An image_placeholder called $name already exists");
                }

                $providers[$name] = $value;
            }
        }

        $container->setParameter('image_alias.placeholder_providers', $providers);
    }

    private function handleUrlChecker($config, ContainerBuilder $container, FileLoader $loader)
    {
        $loader->load('url_checker.yml');
    }

    private function buildPolicyMap(ContainerBuilder $container)
    {
        $policiesBuilder = new PoliciesConfigBuilder($container);
        foreach ($this->policyProviders as $provider) {
            $provider->addPolicies($policiesBuilder);
        }
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

    public function addSiteAccessConfigurationFilter(SiteAccessConfigurationFilter $filter)
    {
        $this->siteaccessConfigurationFilters[] = $filter;
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function registerUrlAliasConfiguration(array $config, ContainerBuilder $container)
    {
        if (!isset($config['url_alias'])) {
            $config['url_alias'] = ['slug_converter' => []];
        }

        $container->setParameter('ezpublish.url_alias.slug_converter', $config['url_alias']['slug_converter']);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function prependTranslatorConfiguration(ContainerBuilder $container)
    {
        if (!$container->hasExtension('framework')) {
            return;
        }

        $fileSystem = new Filesystem();
        $translationsPath = $container->getParameterBag()->get('kernel.root_dir') . '/../vendor/ezplatform-i18n';

        if ($fileSystem->exists($translationsPath)) {
            $container->prependExtensionConfig('framework', ['translator' => ['paths' => [$translationsPath]]]);
        }
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function registerUrlWildcardsConfiguration(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('ezpublish.url_wildcards.enabled', $config['url_wildcards']['enabled'] ?? false);
    }

    /**
     * Loads configuration for UrlWildcardsRouter service if enabled.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Loader\FileLoader $loader
     */
    private function handleUrlWildcards(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
    {
        if ($container->getParameter('ezpublish.url_wildcards.enabled')) {
            $loader->load('url_wildcard.yml');
        }
    }
}
