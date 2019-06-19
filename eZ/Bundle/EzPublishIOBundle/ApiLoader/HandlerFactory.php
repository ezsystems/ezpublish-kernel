<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\ApiLoader;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Factory of IO handlers, given an alias.
 */
class HandlerFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Map of handler id to handler service id.
     *
     * @var array
     */
    private $handlersMap = [];

    public function setHandlersMap($handlersMap)
    {
        $this->handlersMap = $handlersMap;
    }

    /**
     * @param string $handlerName
     *
     * @return object an instance of the requested handler
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException If the requested handler doesn't exist
     */
    public function getConfiguredHandler($handlerName)
    {
        if (!isset($this->handlersMap[$handlerName])) {
            throw new InvalidConfigurationException("Unknown handler $handlerName");
        }

        return $this->container->get($this->handlersMap[$handlerName]);
    }
}
