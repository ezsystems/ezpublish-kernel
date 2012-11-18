<?php
/**
 * File containing CachePool class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\RequestCache;

use ArrayObject;

/**
 * Cache Pool
 *
 * A key value store cache, for use with objects
 *
 */
class CachePool extends ArrayObject
{
    /**
     * The limit of items to hold in the cache
     *
     * @var int
     */
    private $limit;

    /**
     * @param array|null $input
     * @param int $cacheItemLimit
     */
    public function __construct( array $input = null, $cacheItemLimit = 100  )
    {
        $this->limit = $cacheItemLimit;
        parent::__construct( $input );
    }

    /**
     * Set a value
     *
     * @param mixed $key
     * @param mixed $value
     * @return mixed The $value provided as param
     */
    public function set( $key, $value )
    {
        // Check if we have reached the limit of cache items
        if ( !$this->offsetExists( $key ) && $this->count() >= $this->limit )
        {
            $this->exchangeArray(
                array_slice(
                    $this->getArrayCopy(),
                    ((int) $this->limit * 0.3 ),// Remove 30% to avoid having to remove on each set()
                    null,
                    true
                )
            );
        }

        $this->offsetSet( $key, $value );
        return $value;
    }

    /**
     * Return a value by index
     *
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function get( $key )
    {
        return $this->offsetExists( $key ) ?
            $this->offsetGet( $key ) :
            null;
    }

    /**
     * Remove a value
     *
     * @param mixed $key
     */
    public function remove( $key )
    {
        if ( $this->offsetExists( $key ) )
            $this->offsetUnset( $key );
    }

    /**
     * Purge the cache completely
     */
    public function purge()
    {
        $this->exchangeArray( array() );
    }
}
