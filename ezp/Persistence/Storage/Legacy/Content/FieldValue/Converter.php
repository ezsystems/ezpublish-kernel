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
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition;

/**
 * Converter for field values in legacy storage
 */
abstract class Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param FieldValue $value
     * @param StorageFieldValue
     */
    abstract public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue );

    /**
     * Converts data from $value to $fieldValue
     *
     * @param StorageFieldValue $value
     * @param FieldValue $fieldValue
     */
    abstract public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue );

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param FieldDefinition $fieldDef
     * @param StorageFieldDefinition $storageDef
     */
    abstract public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef );

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param StorageFieldDefinition $storageDef
     * @param FieldDefinition $fieldDef
     */
    abstract public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef );

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
