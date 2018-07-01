<?php

/**
 * File containing the ConfigResolverCleanup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Cache\Warmer;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * This cache warmer ensures that ConfigResolver is correctly reset after cache warmup process.
 * @link https://jira.ez.no/browse/EZP-25098
 */
class ConfigResolverCleanup implements CacheWarmerInterface
{
    use ContainerAwareTrait;

    public function isOptional()
    {
        return false;
    }

    public function warmUp($cacheDir)
    {
        $this->container->set('ezpublish.config.resolver.core', null);
        $this->container->set('ezpublish.config.resolver.chain', null);
    }
}
