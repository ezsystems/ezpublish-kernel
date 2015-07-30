<?php

/**
 * File containing the CacheFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class CacheFactory.
 *
 * Service "ezpublish.cache_pool", selects a Stash cache service based on siteaccess[-group] setting "cache_pool_name"
 */
class CacheFactory extends ContainerAware
{
    /**
     * @param ConfigResolverInterface $configResolver
     *
     * @return \Stash\Interfaces\PoolInterface
     */
    public function getCachePool(ConfigResolverInterface $configResolver)
    {
        return $this->container->get(sprintf('stash.%s_cache', $configResolver->getParameter('cache_pool_name')));
    }
}
