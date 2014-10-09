<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as ServiceDefinition;

interface ConfigurationFactory
{
    /**
     * Adds the handler's semantic configuration
     * @param NodeDefinition $nodeDefinition
     */
    public function addConfiguration( NodeDefinition $nodeDefinition );

    /**
     * Returns the ID of the base, abstract service used to create the handlers.
     * @return string
     */
    public function getParentServiceId();

    /**
     * Configures $definition handler service
     *
     * @param ContainerBuilder $container
     * @param ServiceDefinition $definition
     * @param array $config
     */
    public function configureHandler( ContainerBuilder $container, ServiceDefinition $definition, array $config );
}
