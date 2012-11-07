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
    eZ\Publish\Core\MVC\Symfony\Cache\Http\RequestAwarePurger,
    Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache as BaseHttpCache,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request;

abstract class HttpCache extends BaseHttpCache
{
    protected function createStore()
    {
        return new LocationAwareStore( $this->cacheDir ?: $this->kernel->getCacheDir().'/http_cache' );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param bool $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function invalidate( Request $request, $catch = false )
    {
        if ( $request->getMethod() !== 'PURGE' )
        {
            return parent::invalidate( $request, $catch );
        }

        $response = new Response();
        $store = $this->getStore();
        if ( $store instanceof RequestAwarePurger )
        {
            $result = $store->purgeByRequest( $request );
        }
        else
        {
            $result = $store->purge( $request->getUri() );
        }

        if ( $result === true )
        {
            $response->setStatusCode( 200, 'Purged' );
        }
        else
        {
            $response->setStatusCode( 404, 'Not purged' );
        }

        return $response;
    }
}
