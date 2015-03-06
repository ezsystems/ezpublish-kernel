<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Bundle\EzPublishElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected $rootNodeName;

    public function __construct( $rootNodeName )
    {
        $this->rootNodeName = $rootNodeName;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( $this->rootNodeName );

        $this->addConnectionsSection( $rootNode );

        return $treeBuilder;
    }

    /**
     *
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addConnectionsSection( ArrayNodeDefinition $node )
    {
        $node->children()
            ->arrayNode( "connections" )
                ->info( "bla bla bla" )
                //->useAttributeAsKey( "name" )
                ->performNoDeepMerging()
                ->prototype( "array" )
                    ->children()
                        ->scalarNode( "server" )
                            ->isRequired()
                            ->info( "todo server address" )
                            ->example( "https://username:password@hostname.com/path:1234" )
                        ->end()
                        ->scalarNode( "index_name" )
                            ->defaultValue( "ezpublish" )
                            ->info( "" )
                        ->end()
                        ->arrayNode( "type_name" )
                            ->info( "todo todo" )
                            ->example( array( "ezdemo_group" => array( "ezdemo_site", "ezdemo_site_admin" ) ) )
                            ->children()
                                ->scalarNode( "content" )
                                    ->defaultValue( "content" )
                                    ->info( "" )
                                ->end()
                                ->scalarNode( "location" )
                                    ->defaultValue( "location" )
                                    ->info( "" )
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
