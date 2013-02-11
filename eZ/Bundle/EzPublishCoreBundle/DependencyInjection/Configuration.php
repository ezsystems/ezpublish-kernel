<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration implements ConfigurationInterface
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser[]
     */
    private $configParsers;

    public function __construct( array $configParsers )
    {
        $this->configParsers = $configParsers;
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

        $this->addSiteaccessSection( $rootNode );
        $this->addImageMagickSection( $rootNode );
        $this->addHttpCacheSection( $rootNode );
        $this->addSystemSection( $rootNode );

        return $treeBuilder;
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
                                ->beforeNormalization()
                                    // Value passed to the matcher should always be an array.
                                    // If value is not an array, we transform it to a hash, with 'value' as key.
                                    ->ifTrue(
                                        function ( $v )
                                        {
                                            return !is_array( $v );
                                        }
                                    )
                                    ->then(
                                        function ( $v )
                                        {
                                            return array( 'value' => $v );
                                        }
                                    )
                                ->end()
                                ->useAttributeAsKey( 'key' )
                                ->normalizeKeys( false )
                                ->prototype( 'variable' )->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addSystemSection( ArrayNodeDefinition $rootNode )
    {
        $systemNodeBuilder = $rootNode
            ->children()
                ->arrayNode( 'system' )
                    ->info( 'System configuration. First key is always a siteaccess or siteaccess group name' )
                    ->example(
                        array(
                            'ezdemo_site'      => array(
                                'languages'        => array( 'eng-GB', 'fre-FR' ),
                                'content'          => array(
                                    'view_cache'   => true,
                                    'ttl_cache'    => true,
                                    'default_ttl'  => 30
                                )
                            ),
                            'ezdemo_group'     => array(
                                'database' => array(
                                    'type'             => 'mysql',
                                    'server'           => 'localhost',
                                    'port'             => 3306,
                                    'user'             => 'root',
                                    'password'         => 'root',
                                    'database_name'    => 'ezdemo'
                                )
                            )
                        )
                    )
                    ->useAttributeAsKey( 'key' )
                    ->requiresAtLeastOneElement()
                    ->prototype( 'array' )
                        ->children();

        // Delegate to configuration parsers
        foreach ( $this->configParsers as $parser )
        {
            $parser->addSemanticConfig( $systemNodeBuilder );
        }
    }

    private function addImageMagickSection( ArrayNodeDefinition $rootNode )
    {
        $filtersInfo =
<<<EOT
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
#   Cache purge for content/locations is triggered when needed (e.g. on publish) and will result in one or several Http PURGE requests.
#   Can be "local", "single_http" or "multiple_http".
#   If "local" is used, an Http PURGE request will be emulated when needed.
#   If "single" is used, only one Http PURGE request will be sent, with X-Group-Location-Id header (each location id will be separated by "; ".
#   If "multiple" is used, an Http PURGE request will be sent for each location, with X-Location-Id header.
EOT;

        $rootNode
            ->children()
                ->arrayNode( 'http_cache' )
                    ->info( 'Http cache configuration' )
                    ->children()
                        ->scalarNode( 'purge_type' )
                            ->info( $purgeTypeInfo )
                            ->defaultValue( 'local' )
                        ->end()
                        ->scalarNode( 'timeout' )
                            ->info( 'Timeout for each Http PURGE request, in seconds.' )
                            ->validate()
                                ->ifTrue(
                                    function ( $v )
                                    {
                                        return !is_int( $v );
                                    }
                                )
                                ->thenInvalid( 'ezpublish.http_cache.timeout can only be an integer.' )
                            ->end()
                            ->defaultValue( 1 )
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
