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
        $this->addMappingsSection( $rootNode );
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
                ->info( "Solr Search Engine endpoints configuration" )
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
        ;
    }

    /**
     * Adds mappings definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addMappingsSection( ArrayNodeDefinition $node )
    {
        ;
    }
}
