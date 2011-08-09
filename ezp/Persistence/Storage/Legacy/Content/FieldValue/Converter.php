<?php
/**
 * File containing the FieldValue Converter base class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue;

use ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition;

/**
 * Converter for field values in legacy storage
 */
abstract class Converter
{
    /**
     * Converts $value to a StorageFieldValue
     *
     * @param FieldValue $value
     * @return StorageFieldValue
     * @todo Rename toStorageValue()
     */
    abstract public function toStorage( FieldValue $value );

    /**
     * Converts $value to a FieldValue
     *
     * @param StorageFieldValue $value
     * @return FieldValue
     */
    abstract public function toFieldValue( StorageFieldValue $value );

    /**
     * Converts field definition data to a StorageFieldDefinition
     *
     * @param mixed $fieldDef
     * @return StorageFieldDefinition
     */
    abstract public function toStorageFieldDefinition( $fieldDef );

    /**
     * Converts a StorageFieldDefinition to field definition data
     *
     * @param StorageFieldDefinition $storageDef
     * @return mixed
     */
    abstract public function toFieldDefinition( StorageFieldDefinition $storageDef );

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    abstract public function getIndexColumn();
}
