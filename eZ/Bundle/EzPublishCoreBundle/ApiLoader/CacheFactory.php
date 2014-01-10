<?php
/**
 * File containing the CacheFactory class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CacheFactory
 *
 * Service "ezpublish.cache_pool", selects a Stash cache service based on siteaccess[-group] setting "cache_pool_name"
 */
class CacheFactory extends ContainerAware
{
    /**
     * @param ConfigResolverInterface $configResolver
     *
     * @return \Stash\Pool
     */
    public function getCachePool( ConfigResolverInterface $configResolver )
    {
        return $this->container->get( sprintf( 'stash.%s_cache', $configResolver->getParameter( "cache_pool_name" ) ) );
    }
}
