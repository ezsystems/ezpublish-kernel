<?php
/**
 * File containing the BinaryFile converter
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    \ezp\Persistence\Content\FieldValue,
    \ezp\Persistence\Content\Type\FieldDefinition,
    \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition;

class BinaryFile implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue.
     * Nothing has to be stored for eZBinaryFile, as everything has to be stored in an external table.
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        // Nothing is stored here for ezbinaryfile
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        // Nothing to restore
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        if ( isset( $fieldDef->fieldTypeConstraints['FileSizeValidator']['maxFileSize'] ) )
        {
            $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints['FileSizeValidator']['maxFileSize'];
        }
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        if ( !empty( $storageDef->dataInt1 ) )
        {
            $fieldDef->fieldTypeConstraints = array(
                'FileSizeValidator' => array( 'maxFileSize' => $storageDef->dataInt1 )
            );
        }
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return false;
    }

}
