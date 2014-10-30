<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration extends SiteAccessConfiguration
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface
     */
    private $mainConfigParser;

    /**
     * @var Configuration\Suggestion\Collector\SuggestionCollectorInterface
     */
    private $suggestionCollector;

    public function __construct( ParserInterface $mainConfigParser, SuggestionCollectorInterface $suggestionCollector )
    {
        $this->suggestionCollector = $suggestionCollector;
        $this->mainConfigParser = $mainConfigParser;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'ezpublish' );

        $this->addRepositoriesSection( $rootNode );
        $this->addSiteaccessSection( $rootNode );
        $this->addImageMagickSection( $rootNode );
        $this->addHttpCacheSection( $rootNode );
        $this->addPageSection( $rootNode );
        $this->addRouterSection( $rootNode );

        // Delegate SiteAccess config to configuration parsers
        $this->mainConfigParser->addSemanticConfig( $this->generateScopeBaseNode( $rootNode ) );

        return $treeBuilder;
    }

    public function addRepositoriesSection( ArrayNodeDefinition $rootNode )
    {
        $rootNode
            ->children()
                ->arrayNode( 'repositories' )
                    ->info( 'Content repositories configuration' )
                    ->example(
                        array(
                            'main' => array(
                                'engine' => 'legacy',
                                'connection' => 'my_doctrine_connection_name'
                            )
                        )
                    )
                    ->useAttributeAsKey( 'alias' )
                    ->prototype( 'array' )
                        ->beforeNormalization()
                            // If set to null, use default values.
                            // %ezpublish.api.storage_engine.default% as engine, and default connection (if applicable).
                            ->ifNull()
                            ->then(
                                function ()
                                {
                                    return array( 'engine' => '%ezpublish.api.storage_engine.default%', 'connection' => null );
                                }
                            )
                        ->end()
                        ->children()
                            ->scalarNode( 'engine' )->isRequired()->info( 'The storage engine to use' )->end()
                            ->scalarNode( 'connection' )
                                ->info( 'The connection name, if applicable (e.g. Doctrine connection name). If not set, the default connection will be used.' )
                            ->end()
                            ->arrayNode( 'config' )
                                ->info( 'Arbitrary configuration options, supported by your storage engine' )
                                ->useAttributeAsKey( 'key' )
                                ->prototype( 'variable' )->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function addSiteaccessSection( ArrayNodeDefinition $rootNode )
    {
        $rootNode
            ->children()
                ->arrayNode( 'siteaccess' )
                    ->info( 'SiteAccess configuration' )
                    ->children()
                        ->arrayNode( 'list' )
                            ->info( 'Available SiteAccess list' )
                            ->example( array( 'ezdemo_site', 'ezdemo_site_admin' ) )
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype( 'scalar' )->end()
                        ->end()
                        ->arrayNode( 'groups' )
                            ->useAttributeAsKey( 'key' )
                            ->info( 'SiteAccess groups. Useful to share settings between Siteaccess' )
                            ->example( array( 'ezdemo_group' => array( 'ezdemo_site', 'ezdemo_site_admin' ) ) )
                            ->prototype( 'array' )
                                ->requiresAtLeastOneElement()
                                ->prototype( 'scalar' )->end()
                            ->end()
                        ->end()
                        ->scalarNode( 'default_siteaccess' )->isRequired()->info( 'Name of the default siteaccess' )->end()
                        ->arrayNode( 'match' )
                            ->info( 'Siteaccess match configuration. First key is the matcher class, value is passed to the matcher. Key can be a service identifier (prepended by "@"), or a FQ class name (prepended by "\\")' )
                            ->example(
                                array(
                                    'Map\\URI' => array(
                                        'foo' => 'ezdemo_site',
                                        'ezdemo_site' => 'ezdemo_site',
                                        'ezdemo_site_admin' => 'ezdemo_site_admin'
                                    ),
                                    'Map\\Host' => array(
                                        'ezpublish.dev' => 'ezdemo_site',
                                        'admin.ezpublish.dev' => 'ezdemo_site_admin'
                                    ),
                                    '\\My\\Custom\\Matcher' => array(
                                        'some'  => 'configuration'
                                    ),
                                    '@my.custom.matcher' => array(
                                        'some' => 'other_configuration'
                                    )
                                )
                            )
                            ->isRequired()
                            ->useAttributeAsKey( 'key' )
                            ->normalizeKeys( false )
                            ->prototype( 'array' )
                                ->useAttributeAsKey( 'key' )
                                ->beforeNormalization()
                                    ->always(
                                        function ( $v )
                                        {
                                            // Value passed to the matcher should always be an array.
                                            // If value is not an array, we transform it to a hash, with 'value' as key.
                                            if ( !is_array( $v ) )
                                            {
                                                return array( 'value' => $v );
                                            }

                                            // If passed value is a numerically indexed array, we must convert it into a hash.
                                            // See https://jira.ez.no/browse/EZP-21876
                                            if ( array_keys( $v ) === range( 0, count( $v ) - 1 ) )
                                            {
                                                $final = array();
                                                foreach ( $v as $i => $val )
                                                {
                                                    $final["i$i"] = $val;
                                                }

                                                return $final;
                                            }

                                            return $v;
                                        }
                                    )
                                ->end()
                                ->normalizeKeys( false )
                                ->prototype( 'variable' )->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode( 'locale_conversion' )
                    ->info( 'Locale conversion map between eZ Publish format (i.e. fre-FR) to POSIX (i.e. fr_FR). The key is the eZ Publish locale. Check locale.yml in EzPublishCoreBundle to see natively supported locales.' )
                    ->example( array( 'fre-FR' => 'fr_FR' ) )
                    ->useAttributeAsKey( 'key' )
                    ->normalizeKeys( false )
                    ->prototype( 'scalar' )->end()
                ->end()
            ->end();
    }

    private function addImageMagickSection( ArrayNodeDefinition $rootNode )
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
                ->arrayNode( 'imagemagick' )
                    ->info( 'ImageMagick configuration' )
                    ->children()
                        ->booleanNode( 'enabled' )->defaultTrue()->end()
                        ->scalarNode( 'path' )
                            ->info( 'Absolute path of ImageMagick / GraphicsMagick "convert" binary.' )
                            ->beforeNormalization()
                                ->ifTrue(
                                    function ( $v )
                                    {
                                        $basename = basename( $v );
                                        // If there is a space in the basename, just drop it and everything after it.
                                        if ( ( $wsPos = strpos( $basename, ' ' ) ) !== false )
                                        {
                                            $basename = substr( $basename, 0, $wsPos );
                                        }
                                        return !is_executable( dirname( $v ) . DIRECTORY_SEPARATOR . $basename );
                                    }
                                )
                                ->thenInvalid( 'Please provide full path to ImageMagick / GraphicsMagick  "convert" binary. Please also check that it is executable.' )
                            ->end()
                        ->end()
                        ->arrayNode( 'filters' )
                            ->info( $filtersInfo )
                            ->example( array( 'geometry/scaledownonly' => '"-geometry {1}x{2}>"' ) )
                            ->prototype( 'scalar' )->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addHttpCacheSection( ArrayNodeDefinition $rootNode )
    {
        $purgeTypeInfo = <<<EOT
Http cache purge type.

Cache purge for content/locations is triggered when needed (e.g. on publish) and will result in one or several Http PURGE requests.
Can be "local" or "http" or a valid service ID:
- If "local" is used, an Http PURGE request will be emulated when needed (e.g. when using Symfony internal reverse proxy).
- If "http" is used, only one Http BAN request will be sent, with X-Location-Id header containing locationIds to ban.
  X-Location-Id consists in a Regexp containing locationIds to ban.
  Examples:
   - (123|456|789) => Purge locations #123, #456, #789.
   - .* => Purge all locations.
- If a serviceId is provided, it must be defined in the ServiceContainer and must implement eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface.
EOT;

        $rootNode
            ->children()
                ->arrayNode( 'http_cache' )
                    ->children()
                        ->scalarNode( 'purge_type' )
                            ->info( $purgeTypeInfo )
                            ->defaultValue( 'local' )
                            ->beforeNormalization()
                                ->ifTrue(
                                    function ( $v )
                                    {
                                        $http = array( 'multiple_http' => true, 'single_http' => true );
                                        return isset( $http[$v] );
                                    }
                                )
                                ->then(
                                    function ()
                                    {
                                        return 'http';
                                    }
                                )
                            ->end()
                        ->end()
                        ->scalarNode( 'timeout' )->info( 'DEPRECATED' )->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addPageSection( ArrayNodeDefinition $rootNode )
    {
        $pageInfo = <<<EOT
List of globally registered layouts and blocks used by the Page fieldtype
EOT;

        $rootNode
            ->children()
                ->arrayNode( 'ezpage' )
                    ->info( $pageInfo )
                    ->children()
                        ->arrayNode( 'layouts' )
                            ->info( 'List of registered layouts, the key is the identifier of the layout' )
                            ->useAttributeAsKey( 'key' )
                            ->normalizeKeys( false )
                            ->prototype( 'array' )
                                ->children()
                                    ->scalarNode( 'name' )->isRequired()->info( 'Name of the layout' )->end()
                                    ->scalarNode( 'template' )->isRequired()->info( 'Template to use to render this layout' )->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode( 'blocks' )
                            ->info( 'List of registered blocks, the key is the identifier of the block' )
                            ->useAttributeAsKey( 'key' )
                            ->normalizeKeys( false )
                            ->prototype( 'array' )
                                ->children()
                                    ->scalarNode( 'name' )->isRequired()->info( 'Name of the block' )->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode( 'enabledBlocks' )
                            ->prototype( 'scalar' )
                            ->end()
                            ->info( 'List of enabled blocks by default' )
                        ->end()
                        ->arrayNode( 'enabledLayouts' )
                            ->prototype( 'scalar' )
                            ->end()
                            ->info( 'List of enabled layouts by default' )
                        ->end()
                    ->end()
                ->end()
            ->end();

    }

    private function addRouterSection( ArrayNodeDefinition $rootNode )
    {
        $nonSAAwareInfo = <<<EOT
Route names that are not supposed to be SiteAccess aware, i.e. Routes pointing to asset generation (like assetic).
Note that you can just specify a prefix to match a selection of routes.
e.g. "_assetic_" will match "_assetic_*"
Defaults to ['_assetic_', '_wdt', '_profiler', '_configurator_']
EOT;
        $rootNode
            ->children()
                ->arrayNode( 'router' )
                    ->children()
                        ->arrayNode( 'default_router' )
                            ->children()
                                ->arrayNode( 'non_siteaccess_aware_routes' )
                                    ->prototype( 'scalar' )->end()
                                    ->info( $nonSAAwareInfo )
                                    ->example( array( 'my_route_name', 'some_prefix_' ) )
                                ->end()
                                ->arrayNode( 'legacy_aware_routes' )
                                    ->prototype( 'scalar' )->end()
                                    ->info( 'Routes that are allowed when legacy_mode is true. Must be routes identifiers (e.g. "my_route_name"). Can be a prefix, so that all routes beginning with given prefix will be taken into account.' )
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->info( 'Router related settings' )
                ->end()
            ->end();
    }
}
