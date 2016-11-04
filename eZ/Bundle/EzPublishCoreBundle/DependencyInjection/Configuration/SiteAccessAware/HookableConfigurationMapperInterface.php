<?php

/**
 * File containing the HookableConfigurationMapper interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

/**
 * Interface of ConfigurationMapper objects that need to trigger actions before and/or after looping over
 * available scopes for mapping.
 */
interface HookableConfigurationMapperInterface extends ConfigurationMapperInterface
{
    /**
     * This method is called by the ConfigurationProcessor before looping over available scopes.
     * You may here use $contextualizer->mapConfigArray().
     *
     * @see ConfigurationProcessor::mapConfig()
     * @see ContextualizerInterface::mapConfigArray()
     *
     * @param array $config Complete parsed semantic configuration
     * @param ContextualizerInterface $contextualizer
     *
     * @return mixed
     */
    public function preMap(array $config, ContextualizerInterface $contextualizer);

    /**
     * This method is called by the ConfigurationProcessor after looping over available scopes.
     * You may here use $contextualizer->mapConfigArray().
     *
     * @see ConfigurationProcessor::mapConfig()
     * @see ContextualizerInterface::mapConfigArray()
     *
     * @param array $config Complete parsed semantic configuration
     * @param ContextualizerInterface $contextualizer
     *
     * @return mixed
     */
    public function postMap(array $config, ContextualizerInterface $contextualizer);
}
