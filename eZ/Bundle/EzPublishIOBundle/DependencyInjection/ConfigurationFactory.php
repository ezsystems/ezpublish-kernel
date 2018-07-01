<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition as ServiceDefinition;

/**
 * Factory for IO Handlers (metadata or binarydata) configuration.
 *
 * Required to:
 * - register an io handler
 * - add custom semantic configuration below ez_io.xxx_handler.<name>.<type>
 * - customize the custom handler services, and initialize extra services definitions
 */
interface ConfigurationFactory
{
    /**
     * Adds the handler's semantic configuration.
     *
     * Example:
     * ```php
     * $node
     *   ->info( 'my info' )->example( 'an example' )
     *   ->children()
     *     ->scalarNode( 'an_argument' )->info( 'This is an argument' )
     *   ->end();
     * ```
     *
     * @param ArrayNodeDefinition $node The handler's configuration node.
     */
    public function addConfiguration(ArrayNodeDefinition $node);

    /**
     * Returns the ID of the base, abstract service used to create the handlers.
     *
     * It will be used as the base name for instances of this handler, and as the parent of the instances' services.
     *
     * @return string
     */
    public function getParentServiceId();

    /**
     * Configure the handler service based on the configuration.
     *
     * Arguments or calls can be added to the $serviceDefinition, extra services or parameters can be added to the
     * container.
     *
     * Note: if the factory implements ContainerAwareInterface, the ContainerBuilder will be made available as $this->container.
     *
     * @param ServiceDefinition $serviceDefinition
     * @param array $config
     */
    public function configureHandler(ServiceDefinition $serviceDefinition, array $config);
}
