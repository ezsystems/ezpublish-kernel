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
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'ezpublish' );

        $this->addSiteaccessSection( $rootNode );

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
                            ->example( array( 'my_siteaccess', 'my_admin_siteaccess' ) )
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype( 'scalar' )->end()
                        ->end()
                        ->arrayNode( 'groups' )
                            ->useAttributeAsKey( 'key' )
                            ->info( 'SiteAccess groups. Useful to share settings between Siteaccess' )
                            ->example( array( 'my_group' => array( 'my_siteaccess', 'my_admin_siteaccess' ) ) )
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
}
