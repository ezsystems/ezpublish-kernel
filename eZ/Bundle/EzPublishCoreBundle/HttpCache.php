<?php
/**
 * File containing the HttpCache class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\RequestAwarePurger;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache as BaseHttpCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class HttpCache extends BaseHttpCache
{
    public function handle( Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true )
    {
        // X-User-Hash is purely internal and should never be used from outside
        if ( $request->headers->has( 'X-User-Hash' ) )
            $request->headers->remove( 'X-User-Hash' );

        if ( $request->cookies->has( 'user_hash' ) )
        {
            $request->headers->set( 'X-User-Hash', $request->cookies->get( 'user_hash' ) );
        }
        else
        {
            // Forward the request to the kernel to generate the user hash
            $forwardReq = Request::create( '/_ez_user', 'AUTHENTICATE', array(), $request->cookies->all(), array(), $request->server->all() );
            $forwardReq->headers->set( 'X-User-Hash', '' );
            $resp = $this->forward( $forwardReq );
            $request->headers->set( 'X-User-Hash', $resp->headers->get( 'X-User-Hash' ) );
        }

        return parent::handle( $request, $type, $catch );
    }

    protected function createStore()
    {
        return new LocationAwareStore( $this->cacheDir ?: $this->kernel->getCacheDir() . '/http_cache' );
    }

    /**
     * Handle invalidation, including Http PURGE requests.
     * All non-allowed PURGE requests will receive an HTTP 405
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param boolean $catch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function invalidate( Request $request, $catch = false )
    {
        if ( $request->getMethod() !== 'PURGE' )
        {
            return parent::invalidate( $request, $catch );
        }

        // Reject all non-authorized clients
        if ( !$this->isPurgeRequestAllowed( $request ) )
        {
            return new Response( '', 405 );
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

    /**
     * Checks if current purge request is allowed.
     * This method can be overridden to extend the allowance test.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return boolean
     */
    protected function isPurgeRequestAllowed( Request $request )
    {
        if ( !$this->isPurgeIPAllowed( $request->getClientIp() ) )
            return false;

        return true;
    }

    /**
     * Checks if $ip is allowed for Http PURGE requests
     *
     * @todo Check subnets
     *
     * @param string $ip
     *
     * @return boolean
     */
    protected function isPurgeIPAllowed( $ip )
    {
        $allowedIps = array_fill_keys( $this->getPurgeAllowedIPs(), true );
        if ( !isset( $allowedIps[$ip] ) )
            return false;

        return true;
    }

    /**
     * Returns an array of allowed IPs for Http PURGE requests.
     *
     * @return array
     */
    protected function getPurgeAllowedIPs()
    {
        return array( '127.0.0.1', '::1' );
    }
}
