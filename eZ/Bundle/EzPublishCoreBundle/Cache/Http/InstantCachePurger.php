<?php

/**
 * File containing the InstantCachePurger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger as BasePurger;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * @deprecated Http cache is now handled in ezplatform-http-cache and we also don't blindly delete all http cache
 *             on symfony cache clear anymore (clearing can be done via foshttpcache commands). Will be removed in future 7.x FT release.
 */
class InstantCachePurger extends BasePurger implements CacheClearerInterface
{
    /**
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory.
     */
    public function clear($cacheDir)
    {
        $this->purgeAll();
    }
}
