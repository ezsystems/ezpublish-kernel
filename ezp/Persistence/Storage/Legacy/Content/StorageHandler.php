<?php
/**
 * File containing the Storage Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Content\Field;

/**
 * Handler for external storages
 */
class StorageHandler
{
    /**
     * Storage registry
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Array with database context
     *
     * @var array
     */
    protected $context;

    /**
     * Creates a new storage handler
     *
     * @param StorageRegistry $storageRegistry
     * @param array $context
     */
    public function __construct( StorageRegistry $storageRegistry, array $context )
    {
        $this->storageRegistry = $storageRegistry;
        $this->context = $context;
    }

    /**
     * Stores data from $field in its corresponding external storage
     *
     * @param Field $field
     * @return void
     */
    public function storeFieldData( Field $field )
    {
        $storage = $this->storageRegistry->getStorage( $field->type );
        $storage->storeFieldData( $field, $this->context );
    }

    /**
     * Fetches external data for $field from its corresponding external storage
     *
     * @param Field $field
     * @return void
     */
    public function getFieldData( Field $field )
    {
        $storage = $this->storageRegistry->getStorage( $field->type );
        if ( $storage->hasFieldData() )
        {
            $storage->getFieldData( $field, $this->context );
        }
    }

    /**
     * Deletes data for field $ids from external storage of $fieldType
     *
     * @param string $fieldType
     * @param mixed[] $ids
     * @return void
     */
    public function deleteFieldData( $fieldType, array $ids )
    {
        $this->storageRegistry->getStorage( $fieldType )
            ->deleteFieldData( $ids, $this->context );
    }
}
