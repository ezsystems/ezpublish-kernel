<?php
/**
 * File containing the LocalPurgeClient class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * LocalPurgeClient emulates an Http PURGE request received by the cache store.
 * Handy for mono-server.
 */
class LocalPurgeClient implements PurgeClientInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ContentPurger
     */
    protected $cacheStore;

    public function __construct( RequestAwarePurger $cacheStore )
    {
        $this->cacheStore = $cacheStore;
    }

    /**
     * Triggers the cache purge $cacheElements.
     *
     * @param mixed $locationIds Cache resource(s) to purge (e.g. array of URI to purge in a reverse proxy)
     *
     * @return void
     */
    public function purge( $locationIds )
    {
        if ( empty( $locationIds ) )
            return;

        if ( !is_array( $locationIds ) )
            $locationIds = array( $locationIds );

        $purgeRequest = Request::create( 'http://localhost/', 'PURGE' );
        $purgeRequest->headers->set( 'X-Group-Location-Id', implode( '; ', $locationIds ) );
        $this->cacheStore->purgeByRequest( $purgeRequest );
    }

    /**
     * Purges all content elements currently in cache.
     *
     * @return void
     */
    public function purgeAll()
    {
        $this->cacheStore->purgeAllContent();
    }
}
