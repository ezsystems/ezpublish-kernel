<?php
/**
 * File containing the InstantCachePurger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;

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
     *
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
