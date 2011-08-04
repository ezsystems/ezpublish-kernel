<?php
/**
 * File containing the StorageRegistry class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Fields\Storage,
    ezp\Persistence\Storage\Legacy\Exception;

/**
 * Registry for external storages
 */
class StorageRegistry
{
    /**
     * Map of storages
     *
     * @var array
     */
    protected $storageMap = array();

    /**
     * Register a storage
     *
     * @param string $typeName
     * @param Storage $storage
     * @return void
     */
    public function register( $typeName, Storage $storage )
    {
        $this->storageMap[$typeName] = $storage;
    }

    /**
     * Returns the storage for $typeName
     *
     * @param string $typeName
     * @return void
     */
    public function getStorage( $typeName )
    {
        if ( !isset( $this->storageMap[$typeName] ) )
        {
            throw new Exception\StorageNotFoundException( $typeName );
        }
        return $this->storageMap[$typeName];
    }
}
