<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

class PlaceholderAliasGeneratorConfigurator
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry */
    private $providerRegistry;

    /** @var array */
    private $providersConfig;

    /**
     * PlaceholderAliasGeneratorConfigurator constructor.
     *
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry $providerRegistry
     * @param array $providersConfig
     */
    public function __construct(ConfigResolverInterface $configResolver, PlaceholderProviderRegistry $providerRegistry, array $providersConfig)
    {
        $this->configResolver = $configResolver;
        $this->providerRegistry = $providerRegistry;
        $this->providersConfig = $providersConfig;
    }

    public function configure(PlaceholderAliasGenerator $generator)
    {
        $binaryHandlerName = $this->configResolver->getParameter('io.binarydata_handler');

        if (isset($this->providersConfig[$binaryHandlerName])) {
            $providersConfig = $this->providersConfig[$binaryHandlerName];

            $provider = $this->providerRegistry->getProvider($providersConfig['provider']);
            $generator->setPlaceholderProvider($provider, $providersConfig['options']);
        }
    }
}
