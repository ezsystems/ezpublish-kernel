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
                                !empty( $v["cluster"] ) && !is_array( $v["cluster"] )
                            );
                        }
                    )
                    ->then(
                        function( $v )
                        {
                            // If single endpoint is set for cluster, use it as default mapping
                            // for both Content and Location clusters
                            $v["cluster"] = array(
                                "content" => $v["cluster"],
                                "location" => $v["cluster"],
                            );
                            return $v;
                        }
                    )
                ->end()
                ->beforeNormalization()
                    ->ifTrue(
                        function( $v )
                        {
                            return (
                                !empty( $v["cluster"]["content"] ) &&
                                !is_array( $v["cluster"]["content"] )
                            );
                        }
                    )
                    ->then(
                        function( $v )
                        {
                            // If single endpoint is set for Content cluster, use it as default
                            // mapping for Content cluster
                            $v["cluster"]["content"] = array(
                                "default" => $v["cluster"]["content"],
                            );
                            return $v;
                        }
                    )
                ->end()
                ->beforeNormalization()
                    ->ifTrue(
                        function( $v )
                        {
                            return (
                                !empty( $v["cluster"]["location"] ) &&
                                !is_array( $v["cluster"]["location"] )
                            );
                        }
                    )
                    ->then(
                        function( $v )
                        {
                            // If single endpoint is set for Location cluster, use it as default
                            // mapping for Location cluster
                            $v["cluster"]["location"] = array(
                                "default" => $v["cluster"]["location"],
                            );
                            return $v;
                        }
                    )
                ->end()
                ->beforeNormalization()
                    ->ifTrue(
                        function( $v )
                        {
                            return (
                                empty( $v["entry_endpoints"]["content"] ) &&
                                (
                                    !empty( $v["cluster"]["content"]["translations"] ) ||
                                    !empty( $v["cluster"]["content"]["default"] ) ||
                                    !empty( $v["cluster"]["content"]["main_translations"] )
                                )
                            );
                        }
                    )
                    ->then(
                        // If Content search entry endpoints are not provided use
                        // cluster endpoints
                        function( $v )
                        {
                            $endpointSet = array();

                            if ( !empty( $v["cluster"]["content"]["translations"] ) )
                            {
                                $endpointSet = array_flip( $v["cluster"]["content"]["translations"] );
                            }

                            if ( !empty( $v["cluster"]["content"]["default"] ) )
                            {
                                $endpointSet[$v["cluster"]["content"]["default"]] = true;
                            }

                            if ( !empty( $v["cluster"]["content"]["main_translations"] ) )
                            {
                                $endpointSet[$v["cluster"]["content"]["main_translations"]] = true;
                            }

                            $v["entry_endpoints"]["content"] = array_keys( $endpointSet );

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
                                (
                                    !empty( $v["cluster"]["location"]["translations"] ) ||
                                    !empty( $v["cluster"]["location"]["default"] ) ||
                                    !empty( $v["cluster"]["location"]["main_translations"] )
                                )
                            );
                        }
                    )
                    ->then(
                        // If Location search entry endpoints are not provided use
                        // cluster endpoints
                        function( $v )
                        {
                            $endpointSet = array();

                            if ( !empty( $v["cluster"]["location"]["translations"] ) )
                            {
                                $endpointSet = array_flip( $v["cluster"]["location"]["translations"] );
                            }

                            if ( !empty( $v["cluster"]["location"]["default"] ) )
                            {
                                $endpointSet[$v["cluster"]["location"]["default"]] = true;
                            }

                            if ( !empty( $v["cluster"]["location"]["main_translations"] ) )
                            {
                                $endpointSet[$v["cluster"]["location"]["main_translations"]] = true;
                            }

                            $v["entry_endpoints"]["location"] = array_keys( $endpointSet );

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
                                ->info(
                                    "A set of entry endpoint names for the Content index.\n\n" .
                                    "If not set, cluster endpoints will be used."
                                )
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
                                ->info(
                                    "A set of entry endpoint names for the Location index.\n\n" .
                                    "If not set, cluster endpoints will be used."
                                )
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
                        ->info(
                            "Defines a map of translation language codes and Solr " .
                            "endpoint names for Content and Location indexes.\n\n" .
                            "Optionally, you can define default and always available " .
                            "endpoints. Default one will be used for a translation if it " .
                            "is not explicitly mapped, and always available will be used " .
                            "for indexing translations that are always available.\n\n" .
                            "If single endpoint name is given, it will be used as a " .
                            "shortcut to define the default endpoint for both indexes."
                        )
                        ->addDefaultsIfNotSet()
                        ->example(
                            array(
                                "content" => array(
                                    "translations" => array(
                                        "cro-HR" => "endpoint1",
                                        "eng-GB" => "endpoint2",
                                    ),
                                    "default" => "endpoint3",
                                    "main_translations" => "endpoint4",
                                ),
                                "location" => array(
                                    "translations" => array(
                                        "cro-HR" => "endpoint1",
                                        "eng-GB" => "endpoint2",
                                    ),
                                    "default" => "endpoint3",
                                    "main_translations" => "endpoint4",
                                ),
                            )
                        )
                        ->children()
                            ->arrayNode( "content" )
                                ->info(
                                    "Defines a map of translation language codes and Solr " .
                                    "endpoint names for Content index.\n\n" .
                                    "Optionally, you can define default and main translations " .
                                    "endpoints. Default one will be used for a translation if it " .
                                    "is not explicitly mapped, and main translations will be " .
                                    "used for indexing translations in the main language.\n\n" .
                                    "If single endpoint name is given, it will be used as a " .
                                    "shortcut to define the default endpoint."
                                )
                                ->addDefaultsIfNotSet()
                                ->example(
                                    array(
                                        "translations" => array(
                                            "cro-HR" => "endpoint1",
                                            "eng-GB" => "endpoint2",
                                        ),
                                        "default" => "endpoint3",
                                        "main_translations" => "endpoint4",
                                    )
                                )
                                ->children()
                                    ->arrayNode( "translations" )
                                        ->normalizeKeys( false )
                                        ->useAttributeAsKey( "language_code" )
                                            ->info(
                                                "A map of translation language codes and Solr " .
                                                "endpoint names for Content index."
                                            )
                                            ->example(
                                                array(
                                                    "cro-HR" => "endpoint1",
                                                    "eng-GB" => "endpoint2",
                                                )
                                            )
                                        ->prototype( "scalar" )
                                        ->end()
                                    ->end()
                                    ->scalarNode( "default" )
                                        ->defaultNull()
                                        ->info(
                                            "Default endpoint will be used for indexing " .
                                            "documents of a translation that is not explicitly " .
                                            "mapped.\n\n" .
                                            "This setting is optional."
                                        )
                                    ->end()
                                    ->scalarNode( "main_translations" )
                                        ->defaultNull()
                                        ->info(
                                            "Main translations endpoint will be used to index " .
                                            "documents of translations in the main languages\n\n" .
                                            "This setting is optional. Use it to reduce the " .
                                            "number of Solr endpoints that the query is " .
                                            "distributed to when using always available fallback " .
                                            "or searching only main languages."
                                        )
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode( "location" )
                                ->info(
                                    "Defines a map of translation language codes and Solr " .
                                    "endpoint names for Location index.\n\n" .
                                    "Optionally, you can define default and main translations " .
                                    "endpoints. Default one will be used for a translation if it " .
                                    "is not explicitly mapped, and main translations will be " .
                                    "used for indexing translations in the main language.\n\n" .
                                    "If single endpoint name is given, it will be used as a " .
                                    "shortcut to define the default endpoint."
                                )
                                ->addDefaultsIfNotSet()
                                ->example(
                                    array(
                                        "translations" => array(
                                            "cro-HR" => "endpoint1",
                                            "eng-GB" => "endpoint2",
                                        ),
                                        "default" => "endpoint3",
                                        "main_translations" => "endpoint4",
                                    )
                                )
                                ->children()
                                    ->arrayNode( "translations" )
                                        ->normalizeKeys( false )
                                        ->useAttributeAsKey( "language_code" )
                                            ->info(
                                                "A map of translation language codes and Solr " .
                                                "endpoint names for Location index."
                                            )
                                            ->example(
                                                array(
                                                    "cro-HR" => "endpoint1",
                                                    "eng-GB" => "endpoint2",
                                                )
                                            )
                                        ->prototype( "scalar" )
                                        ->end()
                                    ->end()
                                    ->scalarNode( "default" )
                                        ->defaultNull()
                                        ->info(
                                            "Default endpoint will be used for indexing " .
                                            "documents of a translation that is not explicitly " .
                                            "mapped.\n\n" .
                                            "This setting is optional."
                                        )
                                    ->end()
                                    ->scalarNode( "main_translations" )
                                        ->defaultNull()
                                        ->info(
                                            "Main translations endpoint will be used to index " .
                                            "documents of translations in the main languages\n\n" .
                                            "This setting is optional. Use it to reduce the " .
                                            "number of Solr endpoints that the query is " .
                                            "distributed to when using always available fallback " .
                                            "or searching only main languages."
                                        )
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
