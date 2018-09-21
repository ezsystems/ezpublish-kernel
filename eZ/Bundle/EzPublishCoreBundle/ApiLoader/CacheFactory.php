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
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class CacheFactory.
 *
 * Private service "ezpublish.cache_pool_inner", selects a Symfony cache service based on siteaccess[-group] setting "cache_service_name".
 *
 * Will either as alias or decorated returned as part of public "ezpublish.cache_pool".
 */
class CacheFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param ConfigResolverInterface $configResolver
     *
     * @return \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface
     */
    public function getCachePool(ConfigResolverInterface $configResolver)
    {
        /** @var \Symfony\Component\Cache\Adapter\AdapterInterface $cacheService */
        $cacheService = $this->container->get($configResolver->getParameter('cache_service_name'));

        // If cache service is already implementing TagAwareAdapterInterface, return as-is
        if ($cacheService instanceof TagAwareAdapterInterface) {
            return $cacheService;
        }

        return new TagAwareAdapter(
            $cacheService
        );
    }
}
