<?php
/**
 * File containing the CacheServiceDecorator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use Tedivm\StashBundle\Service\CacheService;

/**
 * Class CacheServiceDecorator
 *
 * Wraps the Cache Service for Spi cache to apply key prefix for the cache
 */
class CacheServiceDecorator
{
    const SPI_CACHE_KEY_PREFIX = 'ez_spi';

    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cacheService;

    /**
     * Constructs the cache service decorator
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cacheService
     */
    public function __construct( CacheService $cacheService )
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Returns a Cache item for the specified key. The key can be either a series of string arguments,
     * or an array.
     *
     * @param string|array $key, $key, $key...
     * @return \Stash\Item
     */
    public function getItem()
    {
        $args = func_get_args();

        // check to see if a single array was used instead of multiple arguments, & check empty in case of empty clear()
        if ( empty( $args ) )
            $args = array();
        else if( !isset( $args[1] ) && is_array( $args[0] ) )
            $args = $args[0];

        array_unshift( $args, self::SPI_CACHE_KEY_PREFIX );

        return $this->cacheService->getItem( $args );
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @param null|string|array $key, $key, $key...
     */
    public function clear()
    {
        $item = call_user_func_array( array( $this, 'getItem' ), func_get_args() );
        return $item->clear();
    }
}
