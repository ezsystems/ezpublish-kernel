<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\DependencyInjection\FactoryInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MainHandlerFactory implements FactoryInterface
{
    const MAIN_SEARCH_ENGINE_ID = "ezpublish.spi.search.elasticsearch";
    const HTTP_CLIENT_ID = "ezpublish.search.elasticsearch.content.gateway.client.http.stream";
    const CONTENT_SEARCH_HANDLER_ID = "ezpublish.spi.search.elasticsearch.content_handler";
    const CONTENT_SEARCH_GATEWAY_ID = "ezpublish.search.elasticsearch.content.gateway.native";
    const LOCATION_SEARCH_HANDLER_ID = "ezpublish.spi.search.elasticsearch.location_handler";
    const LOCATION_SEARCH_GATEWAY_ID = "ezpublish.search.elasticsearch.location.gateway.native";

    public function create( ContainerBuilder $container, $context, array $params )
    {
        $searchEngineDef = new DefinitionDecorator( static::MAIN_SEARCH_ENGINE_ID );

        // Create contextualized content search handler
        $searchEngineDef->replaceArgument(
            0,
            new Reference( $this->createContentSearchHandler( $container, $context, $params ) )
        );
        // Create contextualized location search handler
        $searchEngineDef->replaceArgument(
            1,
            new Reference( $this->createLocationSearchHandler( $container, $context, $params ) )
        );
        $searchEngineDef
            ->addTag( 'ezpublish.searchEngine', ["alias" => "elasticsearch.$context"] )
            ->setLazy( true );

        $contextualizedSearchEngineId = static::MAIN_SEARCH_ENGINE_ID . ".$context";
        $container->setDefinition( $contextualizedSearchEngineId, $searchEngineDef );
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    private function createContentSearchHandler( ContainerBuilder $container, $context, array $params )
    {
        $contentSearchHandlerDefinition = new DefinitionDecorator( static::CONTENT_SEARCH_HANDLER_ID );
        $contentSearchHandlerDefinition->replaceArgument(
            0,
            new Reference( $this->createContentSearchGateway( $container, $context, $params ) )
        );
        $contentSearchHandlerDefinition->replaceArgument( 3, $params['document_type_name']['content'] );

        $contentSearchHandlerId = static::CONTENT_SEARCH_HANDLER_ID . ".$context";
        $container->setDefinition( $contentSearchHandlerId, $contentSearchHandlerDefinition );

        return $contentSearchHandlerId;
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    private function createLocationSearchHandler( ContainerBuilder $container, $context, array $params )
    {
        $contentSearchHandlerDefinition = new DefinitionDecorator( static::LOCATION_SEARCH_HANDLER_ID );
        $contentSearchHandlerDefinition->replaceArgument(
            0,
            new Reference( $this->createLocationSearchGateway( $container, $context, $params ) )
        );
        $contentSearchHandlerDefinition->replaceArgument( 3, $params['document_type_name']['location'] );

        $locationSearchHandlerId = static::LOCATION_SEARCH_HANDLER_ID . ".$context";
        $container->setDefinition( $locationSearchHandlerId, $contentSearchHandlerDefinition );

        return $locationSearchHandlerId;
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    private function createContentSearchGateway( ContainerBuilder $container, $context, array $params )
    {
        $contentSearchGatewayDefinition = new DefinitionDecorator( static::CONTENT_SEARCH_GATEWAY_ID );
        $contentSearchGatewayDefinition->replaceArgument(
            0,
            new Reference( $this->createHttpClient( $container, $context, $params ) )
        );
        $contentSearchGatewayDefinition->replaceArgument( 5, $params['index_name'] );

        $contentSearchGatewayId = static::CONTENT_SEARCH_GATEWAY_ID . ".$context";
        $container->setDefinition( $contentSearchGatewayId, $contentSearchGatewayDefinition );

        return $contentSearchGatewayId;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    private function createLocationSearchGateway( ContainerBuilder $container, $context, array $params )
    {
        $contentSearchGatewayDefinition = new DefinitionDecorator( static::LOCATION_SEARCH_GATEWAY_ID );
        $contentSearchGatewayDefinition->replaceArgument(
            0,
            new Reference( $this->createHttpClient( $container, $context, $params ) )
        );
        $contentSearchGatewayDefinition->replaceArgument( 5, $params['index_name'] );

        $contentSearchGatewayId = static::LOCATION_SEARCH_GATEWAY_ID . ".$context";
        $container->setDefinition( $contentSearchGatewayId, $contentSearchGatewayDefinition );

        return $contentSearchGatewayId;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    private function createHttpClient( ContainerBuilder $container, $context, array $params )
    {
        $newHttpClientId = static::HTTP_CLIENT_ID . ".$context";

        if ( $container->hasDefinition( $newHttpClientId ) )
        {
            return $newHttpClientId;
        }

        $httpClientDefinition = new DefinitionDecorator( static::HTTP_CLIENT_ID );
        $httpClientDefinition->replaceArgument( 0, $params['server'] );
        $container->setDefinition( $newHttpClientId, $httpClientDefinition );

        return $newHttpClientId;
    }
}
