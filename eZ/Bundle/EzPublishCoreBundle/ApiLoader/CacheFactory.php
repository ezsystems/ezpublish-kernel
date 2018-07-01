<?php

/**
 * File containing the CacheFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class CacheFactory.
 *
 * Service "ezpublish.cache_pool", selects a Symfony cache service based on siteaccess[-group] setting "cache_service_name"
 */
class CacheFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param ConfigResolverInterface $configResolver
     *
     * @return \Symfony\Component\Cache\Adapter\TagAwareAdapter
     */
    public function getCachePool(ConfigResolverInterface $configResolver)
    {
        return new TagAwareAdapter(
            $this->container->get($configResolver->getParameter('cache_service_name'))
        );
    }
}
