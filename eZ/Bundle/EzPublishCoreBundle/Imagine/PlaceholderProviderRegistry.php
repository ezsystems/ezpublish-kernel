<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use InvalidArgumentException;

class PlaceholderProviderRegistry
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider */
    private $providers;

    /**
     * PlaceholderProviderRegistry constructor.
     *
     * @param array $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    public function addProvider(string $type, PlaceholderProvider $provider)
    {
        $this->providers[$type] = $provider;
    }

    public function supports(string $type): bool
    {
        return isset($this->providers[$type]);
    }

    public function getProvider(string $type): PlaceholderProvider
    {
        if (!$this->supports($type)) {
            throw new InvalidArgumentException("Unknown placeholder provider: $type");
        }

        return $this->providers[$type];
    }
}
