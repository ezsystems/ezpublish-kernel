<?php
/**
 * File containing the Url converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Content\FieldType\Url\Value as UrlValue;

class Url implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $value->data->text;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new UrlValue( "", $value->dataText );
        $fieldValue->data->setState( array( "urlId" => $value->dataInt ) );
        $fieldValue->sortKey = false;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return false
     */
    public function getIndexColumn()
    {
        return false;
    }
}
