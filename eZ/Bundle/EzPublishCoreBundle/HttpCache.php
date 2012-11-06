<?php
/**
 * File containing the HttpCache class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore,
    Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache as BaseHttpCache;

abstract class HttpCache extends BaseHttpCache
{
    protected function createStore()
    {
        return new LocationAwareStore( $this->cacheDir ?: $this->kernel->getCacheDir().'/http_cache' );
    }

}
