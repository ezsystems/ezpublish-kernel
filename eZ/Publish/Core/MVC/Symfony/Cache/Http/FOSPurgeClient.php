<?php
/**
 * File containing the FOSClient class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use FOS\HttpCacheBundle\CacheManager;

class FOSPurgeClient implements PurgeClientInterface
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct( CacheManager $cacheManager )
    {
        $this->cacheManager = $cacheManager;
    }

    public function purge( $locationIds )
    {
        if ( empty( $locationIds ) )
        {
            return;
        }

        if ( !is_array( $locationIds ) )
        {
            $locationIds = array( $locationIds );
        }

        $this->cacheManager->invalidate( array( 'X-Location-Id' => '(' . implode( '|', $locationIds ) . ')' ) );
    }

    public function purgeAll()
    {
        $this->cacheManager->invalidate( array( 'X-Location-Id' => '.*' ) );
    }
}
