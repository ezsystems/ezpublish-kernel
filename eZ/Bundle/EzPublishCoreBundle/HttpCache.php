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
use Stash\Driver\FileSystem;
use Stash\Pool as StashPool;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache as BaseHttpCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class HttpCache extends BaseHttpCache
{
    /**
     * Hash for anonymous user.
     */
    const ANONYMOUS_HASH = '917f736fbbbb1ae450a40be4c1dce175';

    /**
     * Accept header value to be used to request the user hash to the backend application.
     */
    const USER_HASH_ACCEPT_HEADER = 'application/vnd.ez.UserHash+text';

    /**
     * @var Generated user hash.
     */
    private $userHash;

    /**
     * @var Pool
     */
    private $cachePool;

    public function handle( Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true )
    {
        // Forbid direct AUTHENTICATE requests to get user hash
        if (
            $request->headers->get( 'X-HTTP-Override' ) === 'AUTHENTICATE'
            && $request->headers->get( 'Accept' ) === static::USER_HASH_ACCEPT_HEADER
        )
        {
            return new Response( '', 405 );
        }

        if ( $request->isMethodSafe() )
        {
            $request->headers->set( 'X-User-Hash', $this->generateUserHash( $request ) );
        }
        return parent::handle( $request, $type, $catch );
    }

    /**
     * Generates current user hash
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    private function generateUserHash( Request $request )
    {
        if ( isset( $this->userHash ) )
            return $this->userHash;

        // X-User-Hash is purely internal and should never be used from outside
        if ( $request->headers->has( 'X-User-Hash' ) )
            $request->headers->remove( 'X-User-Hash' );

        if ( !$request->cookies->has( 'is_logged_in' ) )
            return $this->userHash = static::ANONYMOUS_HASH;

        // We must have a session at that point since we're supposed to be connected, so HTTP_COOKIE must contain session id.
        // HTTP_COOKIE header will be used as cache key to store the user hash.
        // This will avoid to boot the kernel each time to retrieve the user hash.
        $cookieString = $request->headers->get( 'cookie' );
        $stashItem = $this->getCachePool()->getItem( "ez_user_hash/$cookieString" );
        $userHash = $stashItem->get();
        if ( $stashItem->isMiss() )
        {
            // Forward the request to the kernel to generate the user hash
            $forwardReq = clone $request;
            $forwardReq->headers->set( 'X-HTTP-Override', 'AUTHENTICATE' );
            $forwardReq->headers->set( 'Accept', static::USER_HASH_ACCEPT_HEADER );
            $userHash = $this->forward( $forwardReq )->getContent();
            $stashItem->set( $userHash );
        }

        // Store the user hash in memory for sub-requests (processed in the same thread).
        return $this->userHash = $userHash;
    }

    /**
     * Returns the Stash cache pool.
     *
     * @return \Stash\Pool
     */
    public function getCachePool()
    {
        if ( isset( $this->cachePool ) )
        {
            return $this->cachePool;
        }

        return $this->cachePool = new StashPool(
            new FileSystem(
                array( 'path' => $this->kernel->getCacheDir() . '/stash' )
            )
        );
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
        return array( '127.0.0.1', '::1' );
    }
}
