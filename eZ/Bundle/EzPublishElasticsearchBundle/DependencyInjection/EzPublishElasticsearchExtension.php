<?php
/**
 * File containing the EzPublishElasticsearchExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class EzPublishElasticsearchExtension extends Extension
{
    public function getAlias()
    {
        return "ez_elasticsearch";
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
        $loader->load( "search_engines/elasticsearch.yml" );

        $loader = new YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../Resources/config' )
        );
        $loader->load( 'services.yml' );

        $this->processSearchConfiguration( $container, $config );
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    protected function processSearchConfiguration( ContainerBuilder $container, $config )
    {
        foreach ( $config["connections"] as $name => $params )
        {
            $flattenedParams = $this->flattenParams(
                $params,
                $this->getAlias() . ".connection." . $name
            );

            foreach ( $flattenedParams as $key => $value )
            {
                $container->setParameter( $key, $value );
            }
        }
    }

    /**
     *
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

    /**
     *
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return \eZ\Bundle\EzPublishElasticsearchBundle\DependencyInjection\Configuration
     */
    public function getConfiguration( array $config, ContainerBuilder $container )
    {
        return new Configuration( $this->getAlias() );
    }
}
