<?php
/**
 * File containing the LocalPurgeClient class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\RequestAwarePurger;
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
