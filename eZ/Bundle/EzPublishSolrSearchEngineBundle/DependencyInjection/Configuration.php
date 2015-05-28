<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected $rootNodeName;

    /**
     * Holds default endpoint values
     *
     * @var array
     */
    protected $defaultEndpointValues = array(
        "scheme" => "http",
        "host" => "127.0.0.1",
        "port" => 8983,
        "user" => null,
        "pass" => null,
        "path" => "/solr",
    );

    public function __construct( $rootNodeName )
    {
        $this->rootNodeName = $rootNodeName;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( $this->rootNodeName );

        $this->addEndpointsSection( $rootNode );
        $this->addConnectionsSection( $rootNode );

        return $treeBuilder;
    }

    /**
     * Adds endpoints definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addEndpointsSection( ArrayNodeDefinition $node )
    {
        $node->children()
            ->arrayNode( "endpoints" )
                ->info( "Solr Search Engine endpoint configuration" )
                ->useAttributeAsKey( "endpoint_name" )
                ->performNoDeepMerging()
                ->prototype( "array" )
                    ->beforeNormalization()
                        ->ifTrue(
                            function( $v )
                            {
                                return isset( $v["dsn"] );
                            }
                        )
                        ->then(
                            function( $v )
                            {
                                // Provided DSN will override overlapping standalone values
                                $parts = parse_url( $v["dsn"] );
                                unset( $v["dsn"] );

                                if ( isset( $parts["scheme"] ) ) $v["scheme"] = $parts["scheme"];
                                if ( isset( $parts["host"] ) ) $v["host"] = $parts["host"];
                                if ( isset( $parts["port"] ) ) $v["port"] = $parts["port"];
                                if ( isset( $parts["user"] ) ) $v["user"] = $parts["user"];
                                if ( isset( $parts["pass"] ) ) $v["pass"] = $parts["pass"];
                                if ( isset( $parts["path"] ) ) $v["path"] = $parts["path"];

                                return $v;
                            }
                        )
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode( "scheme" )
                            ->defaultValue( $this->defaultEndpointValues["scheme"] )
                        ->end()
                        ->scalarNode( "host" )
                            ->defaultValue( $this->defaultEndpointValues["host"] )
                        ->end()
                        ->scalarNode( "port" )
                            ->defaultValue( $this->defaultEndpointValues["port"] )
                        ->end()
                        ->scalarNode( "user" )
                            ->defaultValue( $this->defaultEndpointValues["user"] )
                        ->end()
                        ->scalarNode( "pass" )
                            ->defaultValue( $this->defaultEndpointValues["pass"] )
                        ->end()
                        ->scalarNode( "path" )
                            ->defaultValue( $this->defaultEndpointValues["path"] )
                        ->end()
                        ->scalarNode( "core" )
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * Adds connections definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addConnectionsSection( ArrayNodeDefinition $node )
    {
        $node->children()
            ->scalarNode( "default_connection" )
                ->info( "Name of the default connection" )
            ->end()
            ->arrayNode( "connections" )
            ->info( "Solr Search Engine connection configuration" )
            ->useAttributeAsKey( "connection_name" )
            ->performNoDeepMerging()
            ->prototype( "array" )
                ->beforeNormalization()
                    ->ifTrue(
                        function( $v )
                        {
                            return (
                                empty( $v["entry_endpoints"]["content"] ) &&
                                !empty( $v["cluster"]["content"] )
                            );
                        }
                    )
                    ->then(
                        function( $v )
                        {
                            // If Content search entry endpoints are not provided use cluster endpoints
                            $v["entry_endpoints"]["content"] = array_values( $v["cluster"]["content"] );
                            return $v;
                        }
                    )
                ->end()
                ->beforeNormalization()
                    ->ifTrue(
                        function( $v )
                        {
                            return (
                                empty( $v["entry_endpoints"]["location"] ) &&
                                !empty( $v["cluster"]["location"] )
                            );
                        }
                    )
                    ->then(
                        function( $v )
                        {
                            // If Location search entry endpoints are not provided use cluster endpoints
                            $v["entry_endpoints"]["location"] = array_values( $v["cluster"]["location"] );
                            return $v;
                        }
                    )
                ->end()
                ->children()
                    ->arrayNode( "entry_endpoints" )
                        ->info( "A set of entry endpoint names, per search type" )
                        ->addDefaultsIfNotSet()
                        ->example(
                            array(
                                "content" => array(
                                    "endpoint1",
                                    "endpoint2",
                                ),
                                "location" => array(
                                    "endpoint1",
                                    "endpoint2",
                                ),
                            )
                        )
                        ->children()
                            ->arrayNode( "content" )
                                ->info( "A set of endpoint names for Content index. If not set cluster endpoints will be used." )
                                ->example(
                                    array(
                                        "endpoint1",
                                        "endpoint2",
                                    )
                                )
                                ->prototype( "scalar" )
                                ->end()
                            ->end()
                            ->arrayNode( "location" )
                                ->info( "A set of endpoint names for Location index. If not set cluster endpoints will be used." )
                                ->example(
                                    array(
                                        "endpoint1",
                                        "endpoint2",
                                    )
                                )
                                ->prototype( "scalar" )
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode( "cluster" )
                        ->info( "Cluster map, consisting of a mapping of translation language codes and Solr endpoint names, per search type" )
                        ->addDefaultsIfNotSet()
                        ->example(
                            array(
                                "content" => array(
                                    "cro-HR" => "endpoint1",
                                    "eng-GB" => "endpoint2",
                                ),
                                "location" => array(
                                    "cro-HR" => "endpoint1",
                                    "eng-GB" => "endpoint2",
                                ),
                            )
                        )
                        ->children()
                            ->arrayNode( "content" )
                                ->normalizeKeys( false )
                                ->useAttributeAsKey( "language_code" )
                                ->info( "A map of translation language codes and Solr endpoint names for Content index" )
                                ->example(
                                    array(
                                        "cro-HR" => "endpoint1",
                                        "eng-GB" => "endpoint2",
                                    )
                                )
                                ->prototype( "scalar" )
                                ->end()
                            ->end()
                            ->arrayNode( "location" )
                                ->normalizeKeys( false )
                                ->useAttributeAsKey( "language_code" )
                                ->info( "A map of translation language codes and Solr endpoint names for Location index" )
                                ->example(
                                    array(
                                        "cro-HR" => "endpoint1",
                                        "eng-GB" => "endpoint2",
                                    )
                                )
                                ->prototype( "scalar" )
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
