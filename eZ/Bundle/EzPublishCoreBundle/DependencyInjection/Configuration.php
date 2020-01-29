<?php

/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration extends SiteAccessConfiguration
{
    const CUSTOM_TAG_ATTRIBUTE_TYPES = ['number', 'string', 'boolean', 'choice'];

    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface */
    private $mainConfigParser;

    /** @var Configuration\Suggestion\Collector\SuggestionCollectorInterface */
    private $suggestionCollector;

    /** @var \eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessConfigurationFilter[] */
    private $siteAccessConfigurationFilters;

    public function __construct(ParserInterface $mainConfigParser, SuggestionCollectorInterface $suggestionCollector)
    {
        $this->suggestionCollector = $suggestionCollector;
        $this->mainConfigParser = $mainConfigParser;
    }

    public function setSiteAccessConfigurationFilters(array $filters)
    {
        $this->siteAccessConfigurationFilters = $filters;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ezpublish');

        $rootNode = $treeBuilder->getRootNode();

        $this->addRepositoriesSection($rootNode);
        $this->addSiteaccessSection($rootNode);
        $this->addImageMagickSection($rootNode);
        $this->addHttpCacheSection($rootNode);
        $this->addRouterSection($rootNode);
        $this->addUrlAliasSection($rootNode);
        $this->addImagePlaceholderSection($rootNode);
        $this->addUrlWildcardsSection($rootNode);

        // Delegate SiteAccess config to configuration parsers
        $this->mainConfigParser->addSemanticConfig($this->generateScopeBaseNode($rootNode));

        return $treeBuilder;
    }

    public function addRepositoriesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('repositories')
                    ->info('Content repositories configuration')
                    ->example(
                        [
                            'main' => [
                                'storage' => [
                                    'engine' => 'legacy',
                                    'connection' => 'my_doctrine_connection_name',
                                ],
                            ],
                        ]
                    )
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->always(
                                // Handling deprecated structure by mapping it to new one
                                function ($v) {
                                    if (isset($v['storage'])) {
                                        return $v;
                                    }

                                    if (isset($v['engine'])) {
                                        $v['storage']['engine'] = $v['engine'];
                                        unset($v['engine']);
                                    }

                                    if (isset($v['connection'])) {
                                        $v['storage']['connection'] = $v['connection'];
                                        unset($v['connection']);
                                    }

                                    if (isset($v['config'])) {
                                        $v['storage']['config'] = $v['config'];
                                        unset($v['config']);
                                    }

                                    return $v;
                                }
                            )
                        ->end()
                        ->beforeNormalization()
                            ->always(
                                // Setting default values
                                function ($v) {
                                    if ($v === null) {
                                        $v = [];
                                    }

                                    if (!isset($v['storage'])) {
                                        $v['storage'] = [];
                                    }

                                    if (!isset($v['search'])) {
                                        $v['search'] = [];
                                    }

                                    if (!isset($v['fields_groups']['list'])) {
                                        $v['fields_groups']['list'] = [];
                                    }

                                    if (!isset($v['options'])) {
                                        $v['options'] = [];
                                    }

                                    return $v;
                                }
                            )
                        ->end()
                        ->children()
                            ->arrayNode('storage')
                                ->children()
                                    ->scalarNode('engine')
                                        ->defaultValue('%ezpublish.api.storage_engine.default%')
                                        ->info('The storage engine to use')
                                    ->end()
                                    ->scalarNode('connection')
                                        ->defaultNull()
                                        ->info('The connection name, if applicable (e.g. Doctrine connection name). If not set, the default connection will be used.')
                                    ->end()
                                    ->arrayNode('config')
                                        ->info('Arbitrary configuration options, supported by your storage engine')
                                        ->useAttributeAsKey('key')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('search')
                                ->children()
                                    ->scalarNode('engine')
                                        ->defaultValue('%ezpublish.api.search_engine.default%')
                                        ->info('The search engine to use')
                                    ->end()
                                    ->scalarNode('connection')
                                        ->defaultNull()
                                        ->info('The connection name, if applicable (e.g. Doctrine connection name). If not set, the default connection will be used.')
                                    ->end()
                                    ->arrayNode('config')
                                        ->info('Arbitrary configuration options, supported by your search engine')
                                        ->useAttributeAsKey('key')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('fields_groups')
                                ->info('Definitions of fields groups.')
                                ->children()
                                    ->arrayNode('list')->prototype('scalar')->end()->end()
                                    ->scalarNode('default')->defaultValue('%ezsettings.default.content.field_groups.default%')->end()
                                ->end()
                            ->end()
                            ->arrayNode('options')
                                ->info('Options for repository.')
                                ->children()
                                    ->scalarNode('default_version_archive_limit')
                                        ->defaultValue(5)
                                        ->info('Default version archive limit (0-50), only enforced on publish, not on un-publish.')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function addSiteaccessSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('siteaccess')
                    ->info('SiteAccess configuration')
                    ->children()
                        ->arrayNode('list')
                            ->info('Available SiteAccess list')
                            ->example(['ezdemo_site', 'ezdemo_site_admin'])
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('groups')
                            ->useAttributeAsKey('key')
                            ->info('SiteAccess groups. Useful to share settings between Siteaccess')
                            ->example(['ezdemo_group' => ['ezdemo_site', 'ezdemo_site_admin']])
                            ->prototype('array')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->scalarNode('default_siteaccess')->isRequired()->info('Name of the default siteaccess')->end()
                        ->arrayNode('match')
                            ->info('Siteaccess match configuration. First key is the matcher class, value is passed to the matcher. Key can be a service identifier (prepended by "@"), or a FQ class name (prepended by "\\")')
                            ->example(
                                [
                                    'Map\\URI' => [
                                        'foo' => 'ezdemo_site',
                                        'ezdemo_site' => 'ezdemo_site',
                                        'ezdemo_site_admin' => 'ezdemo_site_admin',
                                    ],
                                    'Map\\Host' => [
                                        'ezpublish.dev' => 'ezdemo_site',
                                        'admin.ezpublish.dev' => 'ezdemo_site_admin',
                                    ],
                                    '\\My\\Custom\\Matcher' => [
                                        'some' => 'configuration',
                                    ],
                                    '@my.custom.matcher' => [
                                        'some' => 'other_configuration',
                                    ],
                                ]
                            )
                            ->isRequired()
                            ->useAttributeAsKey('key')
                            ->normalizeKeys(false)
                            ->prototype('array')
                                ->useAttributeAsKey('key')
                                ->beforeNormalization()
                                    ->always(
                                        function ($v) {
                                            // Value passed to the matcher should always be an array.
                                            // If value is not an array, we transform it to a hash, with 'value' as key.
                                            if (!is_array($v)) {
                                                return ['value' => $v];
                                            }

                                            // If passed value is a numerically indexed array, we must convert it into a hash.
                                            // See https://jira.ez.no/browse/EZP-21876
                                            if (array_keys($v) === range(0, count($v) - 1)) {
                                                $final = [];
                                                foreach ($v as $i => $val) {
                                                    $final["i$i"] = $val;
                                                }

                                                return $final;
                                            }

                                            return $v;
                                        }
                                    )
                                ->end()
                                ->normalizeKeys(false)
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->beforeNormalization()
                        ->always()->then(function ($v) {
                            if (isset($this->siteAccessConfigurationFilters)) {
                                foreach ($this->siteAccessConfigurationFilters as $filter) {
                                    $v = $filter->filter($v);
                                }
                            }

                            return $v;
                        })
                    ->end()
                ->end()
                ->arrayNode('locale_conversion')
                    ->info('Locale conversion map between eZ Publish format (i.e. fre-FR) to POSIX (i.e. fr_FR). The key is the eZ Publish locale. Check locale.yml in EzPublishCoreBundle to see natively supported locales.')
                    ->example(['fre-FR' => 'fr_FR'])
                    ->useAttributeAsKey('key')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    private function addImageMagickSection(ArrayNodeDefinition $rootNode)
    {
        $filtersInfo =
<<<EOT
DEPRECATED.
This is only used for legacy injection.
You may use imagick/gmagick liip_imagine bundle drivers.

Hash of filters to be used for your image variations config.
#   Key is the filter name, value is an argument passed to "convert" binary.
#   You can use numbered placeholders (aka input variables) that will be replaced by defined parameters in your image variations config
EOT;

        $rootNode
            ->children()
                ->arrayNode('imagemagick')
                    ->info('ImageMagick configuration')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('path')
                            ->info('Absolute path of ImageMagick / GraphicsMagick "convert" binary.')
                            ->beforeNormalization()
                                ->ifTrue(
                                    function ($v) {
                                        $basename = basename($v);
                                        // If there is a space in the basename, just drop it and everything after it.
                                        if (($wsPos = strpos($basename, ' ')) !== false) {
                                            $basename = substr($basename, 0, $wsPos);
                                        }

                                        return !is_executable(dirname($v) . DIRECTORY_SEPARATOR . $basename);
                                    }
                                )
                                ->thenInvalid('Please provide full path to ImageMagick / GraphicsMagick  "convert" binary. Please also check that it is executable.')
                            ->end()
                        ->end()
                        ->arrayNode('filters')
                            ->info($filtersInfo)
                            ->example(['geometry/scaledownonly' => '"-geometry {1}x{2}>"'])
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addHttpCacheSection(ArrayNodeDefinition $rootNode)
    {
        $purgeTypeInfo = <<<EOT
Http cache purge type.

Cache purge for content/locations is triggered when needed (e.g. on publish) and will result in one or several Http PURGE requests.
Can be "local", "http" or a valid symfony service id:
- If "local" is used, an Http PURGE request will be emulated when needed (e.g. when using Symfony internal reverse proxy).
- If "http" is used, a full HTTP PURGE/BAN is done to a real reverse proxy (Varnish, ..) depending on your config
- If custom symfony service id is used, then check documentation on that service for how it behaves and how you need to configure your system for it.

If ezplatform-http-cache package is enabled (default as of 1.12 and up), then go to documentation on this package for further
info on how it supports multiple response tagging, purges and allow plugins for custom purge types.

If that is not enabled, then the (deprecated as of 1.8) default BAN based system will be used instead.
Where ressponses can be tagged by a single  X-Location-Id header, and for purges a single Http BAN request will be sent,
where X-Location-Id header consists of a Regexp containing locationIds to ban.
  BAN Examples:
   - (123|456|789) => Purge locations #123, #456, #789.
   - .* => Purge all locations.
EOT;

        $rootNode
            ->children()
                ->arrayNode('http_cache')
                    ->children()
                        ->scalarNode('purge_type')
                            ->info($purgeTypeInfo)
                            ->defaultValue('local')
                            ->beforeNormalization()
                                ->ifTrue(
                                    function ($v) {
                                        $http = ['multiple_http' => true, 'single_http' => true];

                                        return isset($http[$v]);
                                    }
                                )
                                ->then(
                                    function () {
                                        return 'http';
                                    }
                                )
                            ->end()
                        ->end()
                        ->scalarNode('timeout')->info('DEPRECATED')->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addRouterSection(ArrayNodeDefinition $rootNode)
    {
        $nonSAAwareInfo = <<<EOT
Route names that are not supposed to be SiteAccess aware, i.e. Routes pointing to asset generation (like assetic).
Note that you can just specify a prefix to match a selection of routes.
e.g. "_assetic_" will match "_assetic_*"
Defaults to ['_assetic_', '_wdt', '_profiler', '_configurator_']
EOT;
        $rootNode
            ->children()
                ->arrayNode('router')
                    ->children()
                        ->arrayNode('default_router')
                            ->children()
                                ->arrayNode('non_siteaccess_aware_routes')
                                    ->prototype('scalar')->end()
                                    ->info($nonSAAwareInfo)
                                    ->example(['my_route_name', 'some_prefix_'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->info('Router related settings')
                ->end()
            ->end();
    }

    /**
     * Defines configuration the images placeholder generation.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     */
    private function addImagePlaceholderSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('image_placeholder')
                    ->info('Configuration for strategy of replacing missing images')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('provider')
                            ->end()
                            ->variableNode('options')
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Define Url Alias Slug converter Semantic Configuration.
     *
     * The configuration is available at:
     * <code>
     * ezpublish:
     *     url_alias:
     *         slug_converter:
     *             transformation: name_of_transformation_group_to_use
     *             separator:  name_of_separator_to_use
     *             transformation_groups:
     *                 transformation_group_name: name of existing or new transformation group
     *                     commands : [] array of commands which will be added to group
     *                     cleanup_method: name_of_cleanup_method
     * </code>
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function addUrlAliasSection(ArrayNodeDefinition $rootNode)
    {
        return $rootNode
            ->children()
                ->arrayNode('url_alias')
                    ->children()
                        ->arrayNode('slug_converter')
                            ->children()
                                ->scalarNode('transformation')->end()
                                ->scalarNode('separator')->end()
                                ->arrayNode('transformation_groups')
                                    ->arrayPrototype()
                                        ->children()
                                            ->arrayNode('commands')
                                                ->scalarPrototype()->end()
                                            ->end()
                                            ->scalarNode('cleanup_method')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Defines configuration for Url Wildcards.
     *
     * The configuration is available at:
     * <code>
     * ezpublish:
     *     url_wildcards:
     *         enabled: true
     * </code>
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function addUrlWildcardsSection($rootNode): ArrayNodeDefinition
    {
        return $rootNode
            ->children()
                ->arrayNode('url_wildcards')
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Enable UrlWildcards support')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
