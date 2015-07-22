<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class EzPublishSolrSearchEngineExtension extends Extension
{
    const MAIN_SEARCH_ENGINE_ID = 'ezpublish.spi.search.solr';
    const CONTENT_SEARCH_HANDLER_ID = 'ezpublish.spi.search.solr.content_handler';
    const CONTENT_SEARCH_GATEWAY_ID = 'ezpublish.search.solr.content.gateway.native';
    const CONTENT_ENDPOINT_RESOLVER_ID = 'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content';
    const LOCATION_SEARCH_HANDLER_ID = 'ezpublish.spi.search.solr.location_handler';
    const LOCATION_SEARCH_GATEWAY_ID = 'ezpublish.search.solr.location.gateway.native';
    const LOCATION_ENDPOINT_RESOLVER_ID = 'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location';

    /**
     * Endpoint class.
     */
    const ENDPOINT_CLASS = 'eZ\\Publish\\Core\\Search\\Solr\\Content\\Gateway\\Endpoint';

    /**
     * Endpoint service tag.
     */
    const ENDPOINT_TAG = 'ezpublish.search.solr.endpoint';

    public function getAlias()
    {
        return 'ez_search_engine_solr';
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
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // Loading configuration from Core/settings
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../../Publish/Core/settings')
        );
        $loader->load('indexable_fieldtypes.yml');
        $loader->load('search_engines/solr.yml');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $this->processConnectionConfiguration($container, $config);
    }

    /**
     * Processes connection configuration by flattening connection parameters
     * and setting them to the container as parameters.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    protected function processConnectionConfiguration(ContainerBuilder $container, $config)
    {
        $alias = $this->getAlias();

        if (isset($config['default_connection'])) {
            $container->setParameter(
                "{$alias}.default_connection",
                $config['default_connection']
            );
        } elseif (!empty($config['connections'])) {
            reset($config['connections']);
            $container->setParameter(
                "{$alias}.default_connection",
                key($config['connections'])
            );
        }

        foreach ($config['connections'] as $name => $params) {
            $this->configureSearchServices($container, $name, $params);
            $container->setParameter("$alias.connection.$name", $params);
        }

        foreach ($config['endpoints'] as $name => $params) {
            $this->defineEndpoint($container, $name, $params);
        }

        // Search engine itself, for given connection name
        $searchEngineDef = $container->findDefinition(self::MAIN_SEARCH_ENGINE_ID);
        $searchEngineDef->setFactory([new Reference('ezpublish.solr.engine_factory'), 'buildEngine']);
    }

    /**
     * Creates needed search services for given connection name and parameters.
     *
     * @param ContainerBuilder $container
     * @param string $connectionName
     * @param array $connectionParams
     */
    private function configureSearchServices(ContainerBuilder $container, $connectionName, $connectionParams)
    {
        $alias = $this->getAlias();

        // Content endpoint resolver
        $contentEndpointResolverId = static::CONTENT_ENDPOINT_RESOLVER_ID . ".$connectionName";
        $contentEndpointResolverDef = new DefinitionDecorator(self::CONTENT_ENDPOINT_RESOLVER_ID);
        $contentEndpointResolverDef->replaceArgument(0, $connectionParams['entry_endpoints']['content']);
        $contentEndpointResolverDef->replaceArgument(1, $connectionParams['cluster']['content']['translations']);
        $contentEndpointResolverDef->replaceArgument(2, $connectionParams['cluster']['content']['default']);
        $contentEndpointResolverDef->replaceArgument(3, $connectionParams['cluster']['content']['main_translations']);
        $container->setDefinition($contentEndpointResolverId, $contentEndpointResolverDef);

        // Content search gateway
        $contentSearchGatewayDef = new DefinitionDecorator(self::CONTENT_SEARCH_GATEWAY_ID);
        $contentSearchGatewayDef->replaceArgument(1, new Reference($contentEndpointResolverId));
        $contentSearchGatewayId = self::CONTENT_SEARCH_GATEWAY_ID . ".$connectionName";
        $container->setDefinition($contentSearchGatewayId, $contentSearchGatewayDef);

        // Content search handler
        $contentSearchHandlerDefinition = new DefinitionDecorator(self::CONTENT_SEARCH_HANDLER_ID);
        $contentSearchHandlerDefinition->replaceArgument(0, new Reference($contentSearchGatewayId));
        $contentSearchHandlerId = self::CONTENT_SEARCH_HANDLER_ID . ".$connectionName";
        $container->setDefinition($contentSearchHandlerId, $contentSearchHandlerDefinition);
        $container->setParameter("$alias.connection.$connectionName.content_handler_id", $contentSearchHandlerId);

        // Location endpoint resolver
        $locationEndpointResolverId = static::LOCATION_ENDPOINT_RESOLVER_ID . ".$connectionName";
        $locationEndpointResolverDef = new DefinitionDecorator(self::CONTENT_ENDPOINT_RESOLVER_ID);
        $locationEndpointResolverDef->replaceArgument(0, $connectionParams['entry_endpoints']['location']);
        $locationEndpointResolverDef->replaceArgument(1, $connectionParams['cluster']['location']['translations']);
        $locationEndpointResolverDef->replaceArgument(2, $connectionParams['cluster']['location']['default']);
        $locationEndpointResolverDef->replaceArgument(3, $connectionParams['cluster']['location']['main_translations']);
        $container->setDefinition($locationEndpointResolverId, $locationEndpointResolverDef);

        // Location search gateway
        $locationSearchGatewayDef = new DefinitionDecorator(self::LOCATION_SEARCH_GATEWAY_ID);
        $locationSearchGatewayDef->replaceArgument(1, new Reference($locationEndpointResolverId));
        $locationSearchGatewayId = self::LOCATION_SEARCH_GATEWAY_ID . ".$connectionName";
        $container->setDefinition($locationSearchGatewayId, $locationSearchGatewayDef);

        // Location search handler
        $locationSearchHandlerDefinition = new DefinitionDecorator(self::LOCATION_SEARCH_HANDLER_ID);
        $locationSearchHandlerDefinition->replaceArgument(0, new Reference($locationSearchGatewayId));
        $locationSearchHandlerId = self::LOCATION_SEARCH_HANDLER_ID . ".$connectionName";
        $container->setDefinition($locationSearchHandlerId, $locationSearchHandlerDefinition);
        $container->setParameter("$alias.connection.$connectionName.location_handler_id", $locationSearchHandlerId);
    }

    /**
     * Creates Endpoint definition in the service container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $alias
     * @param array $params
     */
    protected function defineEndpoint(ContainerBuilder $container, $alias, $params)
    {
        $definition = new Definition(self::ENDPOINT_CLASS, array($params));
        $definition->addTag(self::ENDPOINT_TAG, array('alias' => $alias));

        $container->setDefinition(
            sprintf($this->getAlias() . '.endpoints.%s', $alias),
            $definition
        );
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }
}
