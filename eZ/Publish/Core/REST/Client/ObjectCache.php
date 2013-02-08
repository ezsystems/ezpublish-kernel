<?php
/**
 * File containing the SimpleObjectCache class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Simple cache for value objects during a single request
 */
class ObjectCache
{
    /**
     * Map of cached objects by their key
     *
     * @var array
     */
    protected $cachedObjects = array();

    /**
     * Stores $data under $key
     *
     * @param string $key
     * @param ValueObject $data
     *
     * @return void
     */
    public function store( $key, ValueObject $data )
    {
        $this->cachedObjects[$key] = $data;
    }

    /**
     * Restores data stored under $key, returns null if $key is not found
     *
     * @param string $key
     *
     * @return ValueObject|null
     */
    public function restore( $key )
    {
        if ( isset( $this->cachedObjects[$key] ) )
        {
            return $this->cachedObjects[$key];
        }
        return null;
    }

    /**
     * Clears the data stored in $key
     *
     * @param string $key
     *
     * @return void
     */
    public function clear( $key )
    {
        unset( $this->cachedObjects[$key] );
    }

    /**
     * Clears all cached items
     *
     * @return void
     */
    public function clearAll()
    {
        $this->cachedObjects = array();
    }
}
