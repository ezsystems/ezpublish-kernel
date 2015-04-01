<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Base class for Compiler passes using ConnectionParamFactory.
 *
 * Compiler passes extending this one will usually replace single argument of
 * a specific service with a parameter of a search engine connection, resolved for a
 * current siteaccess.
 */
abstract class ConnectionParameterPass implements CompilerPassInterface
{
    /**
     * ConnectionParameterFactory service container id.
     *
     * @see \eZ\Bundle\EzPublishSolrSearchEngineBundle\ApiLoader\ConnectionParameterFactory
     *
     * @var string
     */
    protected $factoryId = "ezpublish.solr.connection_parameter_factory";

    /**
     * Returns container id of the service that gets its argument replaced.
     *
     * @return string
     */
    abstract protected function getServiceId();

    /**
     * Returns name of the search engine connection parameter that will replace the
     * argument of the service.
     *
     * @return string
     */
    abstract protected function getParameterName();

    /**
     * Returns index of the service's argument to be replaced.
     *
     * @return int
     */
    abstract protected function getReplacedArgumentIndex();

    public function process( ContainerBuilder $container )
    {
        $serviceId = $this->getServiceId();

        if ( !$container->hasDefinition( $serviceId ) )
        {
            return;
        }

        $service = $container->getDefinition( $serviceId );
        $service->replaceArgument(
            $this->getReplacedArgumentIndex(),
            new Reference( $this->injectParameterService( $container, $this->getParameterName() ) )
        );
    }

    /**
     * For given search engine connection parameter with name $parameterName, injects
     * a service resolved through a factory. Service will return parameter's value, resolved
     * for a current siteaccess.
     *
     * @see \eZ\Bundle\EzPublishSolrSearchEngineBundle\ApiLoader\ConnectionParameterFactory
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
