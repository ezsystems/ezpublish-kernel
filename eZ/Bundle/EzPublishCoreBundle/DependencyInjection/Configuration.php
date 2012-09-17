<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface,
    Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
                            ->info( 'Siteaccess match configuration. First key is the matcher class, value is passed to the matcher' )
                            ->example(
                                array(
                                     'Map\URI'      => array(
                                         'foo'  => 'ezdemo_site',
                                         'ezdemo_site' => 'ezdemo_site',
                                         'ezdemo_site_admin' => 'ezdemo_site_admin'
                                     ),
                                     'Map\Host'     => array(
                                         'ezpublish.dev'        => 'ezdemo_site',
                                         'admin.ezpublish.dev'  => 'ezdemo_site_admin'
                                     )
                                )
                            )
                            ->isRequired()
                            ->useAttributeAsKey( 'key' )
                            ->prototype( 'array' )
                                ->beforeNormalization()
                                    // Value passed to the matcher should always be an array.
                                    // If value is not an array, we transform it to a hash, with 'value' as key.
                                    ->ifTrue( function ( $v ) { return !is_array( $v ); } )
                                    ->then( function ( $v ) { return array( 'value' => $v ); } )
                                ->end()
                                ->useAttributeAsKey( 'key' )
                                ->prototype( 'variable' )->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
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
                        ->children()
        ;

        // Delegate to configuration parsers
        foreach ( $this->configParsers as $parser )
        {
            $parser->addSemanticConfig( $systemNodeBuilder );
        }
    }
}
