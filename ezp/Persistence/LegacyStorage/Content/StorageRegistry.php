<?php
/**
 * File containing the StorageRegistry class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\LegacyStorage\Content;

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
     * @param StorageInterface $storage
     * @return void
     */
    public function register( $typeName, StorageInterface $storage )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Returns the storage for $typeName
     *
     * @param string $typeName
     * @return void
     */
    public function getStorage( $typeName )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
