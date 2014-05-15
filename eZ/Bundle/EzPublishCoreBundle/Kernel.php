<?php
/**
 * File containing the Kernel class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Stash\Driver\FileSystem;
use Stash\Pool as StashPool;

abstract class Kernel extends BaseKernel
{
    /**
     * Hash for anonymous user.
     */
    const ANONYMOUS_HASH = '38015b703d82206ebc01d17a39c727e5';

    /**
     * Accept header value to be used to request the user hash to the backend application.
     */
    const USER_HASH_ACCEPT_HEADER = 'application/vnd.ez.UserHash+text';

    /**
     * Prefix for session name.
     */
    const SESSION_NAME_PREFIX = 'eZSESSID';

    /**
     * Generated user hash.
     *
     * @var string
     */
    private $userHash;

    /**
     * @var \Stash\Interfaces\PoolInterface
     */
    private $cachePool;

    /**
     * Flag indicating if the user hash is being generated.
     *
     * @var bool
     */
    private $generatingUserHash = false;

    public function handle( Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true )
    {
        $isUserHashRequest = $this->isUserHashRequest( $request );
        if ( $isUserHashRequest && !$this->canGenerateUserHash( $request ) )
        {
            return new Response( '', 405 );
        }
        else if ( $isUserHashRequest && !$this->generatingUserHash )
        {
            return new Response( '', 200, array( 'X-User-Hash' => $this->generateUserHash( $request ) ) );
        }

        return parent::handle( $request, $type, $catch );
    }

    /**
     * Checks if $request is for pre-authentication (to generate current user's hash).
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isUserHashRequest( Request $request )
    {
        return
            $request->headers->get( 'X-HTTP-Override' ) === 'AUTHENTICATE'
            && $request->headers->get( 'Accept' ) === static::USER_HASH_ACCEPT_HEADER;
    }

    /**
     * Checks if current request is allowed to generate the user hash.
     * Default behavior is to accept values set in TRUSTED_PROXIES env variable and local IP addresses:
     *  - 127.0.0.1
     *  - ::1
     *  - fe80::1
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function canGenerateUserHash( Request $request )
    {
        $trustedProxies = array_unique(
            array_merge(
                Request::getTrustedProxies(),
                array(
                    '127.0.0.1',
                    '::1',
                    'fe80::1'
                )
            )
        );
        return
            $request->attributes->get( 'internalRequest' )
            || in_array( $request->getClientIp(), $trustedProxies );
    }

    /**
     * Generates current user hash
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    public function generateUserHash( Request $request )
    {
        if ( isset( $this->userHash ) )
            return $this->userHash;

        // X-User-Hash is purely internal and should never be used from outside
        $request->headers->remove( 'X-User-Hash' );

        if ( $this->isAnonymous( $request ) )
        {
            return $this->userHash = static::ANONYMOUS_HASH;
        }

        // Try to retrieve user hash in cache first.
        // This will avoid to boot the kernel each time to retrieve the user hash.
        $stashItem = $this->getCachePool()->getItem( 'ez_user_hash/' . $this->getUserHashCacheKey( $request ) );
        $this->userHash = $stashItem->get();
        if ( $stashItem->isMiss() )
        {
            // Forward the request to the kernel to generate the user hash
            $forwardReq = clone $request;
            $forwardReq->headers->set( 'X-HTTP-Override', 'AUTHENTICATE' );
            $forwardReq->headers->set( 'Accept', static::USER_HASH_ACCEPT_HEADER );
            $forwardReq->attributes->set( 'internalRequest', true );
            $this->generatingUserHash = true;
            $resp = $this->handle( $forwardReq );
            if ( !$resp->headers->has( 'X-User-Hash' ) )
            {
                trigger_error( 'Could not generate user hash ! Fallback to anonymous hash.', E_USER_WARNING );
            }
            $this->userHash = $resp->headers->get( 'X-User-Hash' );
            $stashItem->set( $this->userHash, $this->getUserHashCacheTtl() );
            $this->generatingUserHash = false;
        }

        // Store the user hash in memory for sub-requests (processed in the same thread).
        return $this->userHash;
    }

    /**
     * Checks if current request is considered anonymous.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isAnonymous( Request $request )
    {
        foreach ( $request->cookies as $name => $value )
        {
            if ( $this->isSessionName( $name ) )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the Stash cache pool (for early requests like user hash generation).
     *
     * @return \Stash\Interfaces\PoolInterface
     */
    public function getCachePool()
    {
        if ( isset( $this->cachePool ) )
        {
            return $this->cachePool;
        }

        return $this->cachePool = new StashPool( $this->getCacheDriver() );
    }

    /**
     * Returns the cache driver to use for the Stash pool.
     * Override this method if you prefer to use another driver (e.g. \Stash\Driver\Apc).
     *
     * @see getCachePool
     *
     * @return \Stash\Interfaces\DriverInterface
     */
    protected function getCacheDriver()
    {
        return new FileSystem(
            array( 'path' => $this->getCacheDir() . '/stash' )
        );
    }

    /**
     * Returns the number of seconds the user hash is considered fresh in cache.
     *
     * @return int
     */
    protected function getUserHashCacheTtl()
    {
        return 600;
    }

    /**
     * Returns the cache key under which the user hash will be stored.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    protected function getUserHashCacheKey( Request $request )
    {
        // There can be several session cookies (i.e. session name beginning by static::SESSION_NAME_PREFIX) for the same
        // domain / path as we can have several siteaccesses sharing the same session context.
        // Hence we concatenate session Ids together.
        $cacheKeyArray = array();
        foreach ( $request->cookies as $name => $value )
        {
            if ( $this->isSessionName( $name ) )
            {
                $cacheKeyArray[] = $value;
            }
        }

        return implode( '|', $cacheKeyArray );
    }

    /**
     * Checks if passed string can be considered as a session name, such as would be used in cookies.
     *
     * @param string $name
     *
     * @return bool
     */
    private function isSessionName( $name )
    {
        return strpos( $name, static::SESSION_NAME_PREFIX ) === 0;
    }
}
