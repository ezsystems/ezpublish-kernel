<?php
/**
 * File containing the InstantCachePurger class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger,
    eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;

class InstantCachePurger implements GatewayCachePurger
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface
     */
    private $purgeClient;

    public function __construct( PurgeClientInterface $purgeClient )
    {
        $this->purgeClient = $purgeClient;
    }

    /**
     * Instantly triggers the cache purge of given $cacheElements.
     *
     * @param mixed $cacheElements
     * @return mixed
     */
    public function purge( $cacheElements )
    {
        $this->purgeClient->purge( $cacheElements );

        return $cacheElements;
    }

    public function purgeAll()
    {
        $this->purgeClient->purgeAll();
    }
}
