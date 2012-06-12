<?php
/**
 * File containing the StorageRegistry class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content;
use eZ\Publish\SPI\Persistence\Fields\Storage,
    eZ\Publish\Core\Persistence\Legacy\Exception,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\NullStorage;

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
     * @return Storage
     */
    public function getStorage( $typeName )
    {
        if ( !isset( $this->storageMap[$typeName] ) )
        {
            $this->register( $typeName, new NullStorage );
        }
        return $this->storageMap[$typeName];
    }
}
