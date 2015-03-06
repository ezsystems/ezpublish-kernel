<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 */
abstract class ConnectionParameterPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    protected $factoryId = "ezpublish.elasticsearch.connection_param_factory";

    /**
     *
     *
     * @return string
     */
    abstract protected function getServiceId();

    /**
     *
     *
     * @return string
     */
    abstract protected function getParameterName();

    /**
     *
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

        $httpClientServiceDefinition = $container->getDefinition( $serviceId );
        $httpClientServiceDefinition->replaceArgument(
            $this->getReplacedArgumentIndex(),
            new Reference( $this->injectParameterService( $container, $this->getParameterName() ) )
        );
    }

    /**
     *
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $parameterName
     *
     * @return string
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
