<?php
/**
 * File containing the Checkbox converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Content\FieldType\Checkbox\Value as CheckboxValue,
    ezp\Content\FieldType\FieldSettings;

class Checkbox implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataInt = (int)$value->data->bool;
        $storageFieldValue->sortKeyInt = $value->sortKey['sort_key_int'];
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new CheckboxValue( (bool)$value->dataInt );
        $fieldValue->sortKey = array( 'sort_key_int' => $value->sortKeyInt );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        $storageDef->dataInt3 = (int)$fieldDef->fieldTypeConstraints->fieldSettings['defaultValue'];
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints;
        $defaultValue = $storageDef->dataInt3;
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultValue' => (bool)$defaultValue
            )
        );
        $fieldDef->defaultValue = new FieldValue(
            array( 'data' => new CheckboxValue( (bool)$defaultValue ) )
        );
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
        return 'sort_key_int';
    }

}
