<?php
/**
 * File containing the CacheServiceDecorator class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use Stash\Interfaces\PoolInterface;

/**
 * Class CacheServiceDecorator
 *
 * Wraps the Cache Service for Spi cache to apply key prefix for the cache
 */
class CacheServiceDecorator
{
    const SPI_CACHE_KEY_PREFIX = 'ez_spi';

    /**
     * @var \Stash\Interfaces\PoolInterface
     */
    protected $cachePool;

    /**
     * Constructs the cache service decorator
     *
     * @param \Stash\Interfaces\PoolInterface $cachePool
     */
    public function __construct( PoolInterface $cachePool )
    {
        $this->cachePool = $cachePool;
    }

    /**
     * Returns a Cache item for the specified key. The key can be either a series of string arguments,
     * or an array.
     *
     * @internal param array|string $key , $key, $key...
     * @return \Stash\Interfaces\ItemInterface
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

        return $this->cachePool->getItem( $args );
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @internal param array|null|string $key , $key, $key...
     */
    public function clear()
    {
        $args = func_get_args();

        // Do a purge, but with cache key in case of no arguments
        if ( empty( $args ) )
            return $this->cachePool->getDriver()->clear( array( self::SPI_CACHE_KEY_PREFIX ) );

        $item = call_user_func_array( array( $this, 'getItem' ), $args );
        return $item->clear();
    }
}
