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

/**
 *
 */
class MainHandlerFactory implements FactoryInterface
{
    protected function getMainSearchEngineId()
    {
        return "ezpublish.spi.search.elasticsearch";
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     */
    public function create( ContainerBuilder $container, $context )
    {
        $mainSearchHandlerId = $this->createMainSearchHandler( $container, $context );

        $container
            ->getDefinition( $mainSearchHandlerId )
            ->addTag(
                'ezpublish.searchEngine',
                array(
                    "alias" => "elasticsearch",
                )
            );
    }

    /**
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    protected function createMainSearchHandler( ContainerBuilder $container, $context )
    {
        $mainSearchEngineId = $this->getMainSearchEngineId();
        $mainSearchEngineDefinition = new DefinitionDecorator( $mainSearchEngineId );

        $mainSearchEngineDefinition->replaceArgument(
            0,
            new Reference( $this->createContentSearchHandler( $container, $context ) )
        );

        $mainSearchEngineDefinition->replaceArgument(
            1,
            new Reference( $this->createLocationSearchHandler( $container, $context ) )
        );

        $mainSearchEngineId .= "." . $context;

        $container->setDefinition( $mainSearchEngineId, $mainSearchEngineDefinition );

        return $mainSearchEngineId;
    }

    protected function getContentSearchHandlerId()
    {
        return "ezpublish.spi.search.elasticsearch.content_handler";
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    protected function createContentSearchHandler( ContainerBuilder $container, $context )
    {
        $contentSearchHandlerId = $this->getContentSearchHandlerId();
        $contentSearchHandlerDefinition = new DefinitionDecorator( $contentSearchHandlerId );

        $contentSearchHandlerDefinition->replaceArgument(
            0,
            new Reference( $this->createContentSearchGateway( $container, $context ) )
        );

        $contentSearchHandlerDefinition->replaceArgument(
            3,
            new Reference( $this->createContentTypeDocumentName( $container, $context ) )
        );

        $contentSearchHandlerId .= "." . $context;

        $container->setDefinition( $contentSearchHandlerId, $contentSearchHandlerDefinition );

        return $contentSearchHandlerId;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return string
     */
    protected function createContentTypeDocumentName( ContainerBuilder $container )
    {
        return $this->injectParameterService( $container, "document_type_name.content" );
    }

    protected function getLocationSearchHandlerId()
    {
        return "ezpublish.spi.search.elasticsearch.location_handler";
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    protected function createLocationSearchHandler( ContainerBuilder $container, $context )
    {
        $contentSearchHandlerId = $this->getContentSearchHandlerId();
        $contentSearchHandlerDefinition = new DefinitionDecorator( $contentSearchHandlerId );

        $contentSearchHandlerDefinition->replaceArgument(
            0,
            new Reference( $this->createLocationSearchGateway( $container, $context ) )
        );

        $contentSearchHandlerDefinition->replaceArgument(
            3,
            new Reference( $this->createLocationTypeDocumentName( $container, $context ) )
        );

        $contentSearchHandlerId .= "." . $context;

        $container->setDefinition( $contentSearchHandlerId, $contentSearchHandlerDefinition );

        return $contentSearchHandlerId;
    }

    /**
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return string
     */
    protected function createLocationTypeDocumentName( ContainerBuilder $container )
    {
        return $this->injectParameterService( $container, "document_type_name.location" );
    }

    protected function getContentSearchGatewayId()
    {
        return "ezpublish.search.elasticsearch.content.gateway";
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    protected function createContentSearchGateway( ContainerBuilder $container, $context )
    {
        $contentSearchGatewayId = $this->getContentSearchGatewayId();
        $contentSearchGatewayDefinition = new DefinitionDecorator( $contentSearchGatewayId );

        $contentSearchGatewayDefinition->replaceArgument(
            0,
            new Reference( $this->createHttpClient( $container, $context ) )
        );

        $contentSearchGatewayDefinition->replaceArgument(
            5,
            new Reference( $this->createIndexName( $container, $context ) )
        );

        $contentSearchGatewayId .= "." . $context;

        $container->setDefinition( $contentSearchGatewayId, $contentSearchGatewayDefinition );

        return $contentSearchGatewayId;
    }

    protected function getLocationSearchGatewayId()
    {
        return "ezpublish.search.elasticsearch.location.gateway";
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    protected function createLocationSearchGateway( ContainerBuilder $container, $context )
    {
        $contentSearchGatewayId = $this->getContentSearchGatewayId();
        $contentSearchGatewayDefinition = new DefinitionDecorator( $contentSearchGatewayId );

        $contentSearchGatewayDefinition->replaceArgument(
            0,
            new Reference( $this->createHttpClient( $container, $context ) )
        );

        $contentSearchGatewayDefinition->replaceArgument(
            5,
            new Reference( $this->createIndexName( $container, $context ) )
        );

        $contentSearchGatewayId .= "." . $context;

        $container->setDefinition( $contentSearchGatewayId, $contentSearchGatewayDefinition );

        return $contentSearchGatewayId;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return string
     */
    protected function createIndexName( ContainerBuilder $container )
    {
        return $this->injectParameterService( $container, "index_name" );
    }

    protected function getHttpClientId()
    {
        return "ezpublish.search.elasticsearch.content.gateway.client.http.stream";
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $context
     *
     * @return string
     */
    protected function createHttpClient( ContainerBuilder $container, $context )
    {
        $httpClientId = $this->getHttpClientId();
        $newHttpClientId = $httpClientId . "." . $context;

        if ( $container->hasDefinition( $newHttpClientId ) )
        {
            return $newHttpClientId;
        }

        $httpClientDefinition = new DefinitionDecorator( $httpClientId );

        $httpClientDefinition->replaceArgument(
            0,
            new Reference( $this->createServerAddress( $container, $context ) )
        );

        $container->setDefinition( $newHttpClientId, $httpClientDefinition );

        return $newHttpClientId;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return string
     */
    protected function createServerAddress( ContainerBuilder $container )
    {
        return $this->injectParameterService( $container, "server" );
    }

    /**
     * ConnectionParameterFactory service container id.
     *
     * @see \eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\ApiLoader\ConnectionParameterFactory
     *
     * @var string
     */
    protected $factoryId = "ezpublish.elasticsearch.connection_parameter_factory";

    /**
     * For given search engine connection parameter with name $parameterName, injects
     * a service resolved through a factory. Service will return parameter's value, resolved
     * for a current siteaccess.
     *
     * @see \eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\ApiLoader\ConnectionParameterFactory
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $parameterName
     *
     * @return string Container id of the injected service.
     */
    protected function injectParameterService( ContainerBuilder $container, $parameterName )
    {
        $paramConverter = new Definition( "stdClass" );
        $paramConverter
            ->setFactory(
                array(
                    new Reference( $this->factoryId ),
                    "getParameter",
                )
            )
            ->setArguments( array( $parameterName ) );

        $serviceId = "{$this->factoryId}.{$parameterName}";

        if ( !$container->hasDefinition( $serviceId ) )
        {
            $container->setDefinition( $serviceId, $paramConverter );
        }

        return $serviceId;
    }
}
