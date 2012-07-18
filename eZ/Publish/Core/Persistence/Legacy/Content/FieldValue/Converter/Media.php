<?php
/**
 * File containing the Media converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\FieldType\Media\Value as MediaValue,
    eZ\Publish\Core\FieldType\FieldSettings;

class Media implements Converter
{
    const FILESIZE_VALIDATOR_IDENTIFIER = "FileSizeValidator";

    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @static
     * @return Media
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Converts data from $value to $storageFieldValue.
     * Nothing has to be stored for eZMedia, as everything has to be stored in an external table.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        // Nothing is stored here for ezmedia
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        // Nothing to restore
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        if ( isset( $fieldDef->fieldTypeConstraints->validators[self::FILESIZE_VALIDATOR_IDENTIFIER]['maxFileSize'] ) )
        {
            $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints->validators[self::FILESIZE_VALIDATOR_IDENTIFIER]['maxFileSize'];
        }

        $storageDef->dataText1 = $fieldDef->fieldTypeConstraints->fieldSettings['mediaType'];
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        if ( !empty( $storageDef->dataInt1 ) )
        {
            $fieldDef->fieldTypeConstraints->validators = array(
                self::FILESIZE_VALIDATOR_IDENTIFIER => array( 'maxFileSize' => $storageDef->dataInt1 )
            );
        }

        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array( 'mediaType' => $storageDef->dataText1 )
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
        return false;
    }

}
