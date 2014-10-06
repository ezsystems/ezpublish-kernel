<?php
/**
 * File containing the HttpCache class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\RequestAwarePurger;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache as BaseHttpCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class HttpCache extends BaseHttpCache
{
    public function handle( Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true )
    {
        // Forbid direct AUTHENTICATE requests to get user hash
        if (
            $request->headers->get( 'X-HTTP-Override' ) === 'AUTHENTICATE'
            && $request->headers->get( 'Accept' ) === Kernel::USER_HASH_ACCEPT_HEADER
        )
        {
            return new Response( '', 405 );
        }

        if ( $request->isMethodSafe() )
        {
            $request->headers->set( 'X-User-Hash', $this->kernel->generateUserHash( $request ) );
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
        if ( $request->getMethod() !== 'PURGE' && $request->getMethod() !== 'BAN' )
        {
            return parent::invalidate( $request, $catch );
        }

        // Reject all non-authorized clients
        if ( !$this->isInternalRequestAllowed( $request ) )
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
    protected function isInternalRequestAllowed( Request $request )
    {
        if ( !$this->isInternalIPAllowed( $request->getClientIp() ) )
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
    protected function isInternalIPAllowed( $ip )
    {
        $allowedIps = array_fill_keys( $this->getInternalAllowedIPs(), true );
        if ( !isset( $allowedIps[$ip] ) )
            return false;

        return true;
    }

    /**
     * Returns an array of allowed IPs for Http PURGE requests.
     *
     * @return array
     */
    protected function getInternalAllowedIPs()
    {
        return array( '127.0.0.1', '::1', 'fe80::1' );
    }
}
