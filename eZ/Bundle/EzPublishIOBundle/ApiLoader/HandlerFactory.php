<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\ApiLoader;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HandlerFactory
{
    private $handlersMap = array();

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer( ContainerInterface $container )
    {
        $this->container = $container;
    }

    public function setHandlersMap( $handlersMap )
    {
        $this->handlersMap = $handlersMap;
    }

    public function getConfiguredHandler( $handlerName )
    {
        if ( !isset( $this->handlersMap[$handlerName] ) )
        {
            throw new InvalidConfigurationException( "Unknown handler $handlerName" );
        }

        return $this->container->get( $this->handlersMap[$handlerName] );
    }
}
