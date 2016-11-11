<?php

/**
 * File containing the ScopeConfigurationMapper interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

/**
 * ConfigurationMapper purpose is to map parsed semantic configuration for given scope
 * (SiteAccess, SiteAccess group or "global") to internal container parameters with the appropriate format.
 *
 * ConfigurationMapper needs to be passed to `ConfigurationProcessor::mapConfig()`.
 *
 * @see ConfigurationProcessor::mapConfig()
 */
interface ConfigurationMapperInterface
{
    /**
     * Does semantic config to internal container parameters mapping for $currentScope.
     *
     * This method is called by the `ConfigurationProcessor`, for each available scopes (e.g. SiteAccess, SiteAccess groups or "global").
     *
     * @param array $scopeSettings Parsed semantic configuration for current scope.
     *                             It is passed by reference, making it possible to alter it for usage after `mapConfig()` has run.
     * @param string $currentScope
     * @param ContextualizerInterface $contextualizer
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer);
}
