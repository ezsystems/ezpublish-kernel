<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class EzPublishSolrSearchEngineExtension extends Extension
{
    public function getAlias()
    {
        return "ez_search_engine_solr";
    }

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $configuration = $this->getConfiguration( $configs, $container );
        $config = $this->processConfiguration( $configuration, $configs );

        // Loading configuration from Core/settings
        $loader = new YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../../../Publish/Core/settings' )
        );
        $loader->load( 'indexable_fieldtypes.yml' );
        $loader->load( "search_engines/solr.yml" );

        $loader = new YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../Resources/config' )
        );
        $loader->load( 'services.yml' );

        $this->processConnectionConfiguration( $container, $config );
    }

    /**
     * Processes connection configuration by flattening connection parameters
     * and setting them to the container as parameters.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    protected function processConnectionConfiguration( ContainerBuilder $container, $config )
    {
        $alias = $this->getAlias();

        if ( isset( $config["default_connection"] ) )
        {
            $container->setParameter(
                "{$alias}.default_connection",
                $config["default_connection"]
            );
        }

        foreach ( $config["connections"] as $name => $params )
        {
            $flattenedParams = $this->flattenParams(
                $params,
                "{$alias}.connection.{$name}"
            );

            foreach ( $flattenedParams as $key => $value )
            {
                $container->setParameter( $key, $value );
            }
        }
    }

    /**
     * Flattens nested array structure into a single level key-value array, concatenating
     * keys through levels and keeping values.
     *
     * @param array $nestedParams
     * @param string $prefix
     *
     * @return array
     */
    protected function flattenParams( $nestedParams, $prefix )
    {
        $params = array();

        foreach ( $nestedParams as $key => $value )
        {
            if ( is_array( $value ) )
            {
                $params = $params + $this->flattenParams( $value, $prefix . "." . $key );
            }
            else
            {
                $params[$prefix . "." . $key] = $value;
            }
        }

        return $params;
    }

    public function getConfiguration( array $config, ContainerBuilder $container )
    {
        return new Configuration( $this->getAlias() );
    }
}
