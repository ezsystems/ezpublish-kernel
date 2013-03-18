<?php
/**
 * File containing the Time field value converter class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\FieldType\Time\Type as TimeType;
use eZ\Publish\Core\FieldType\FieldSettings;
use DateTime;

/**
 * Time field value converter class
 */
class Time implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataInt = ( $value->data !== null ? $value->data : null );
        $storageFieldValue->sortKeyInt = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        if ( $value->dataInt === null )
        {
            return;
        }

        $fieldValue->data = $value->dataInt;
        $fieldValue->sortKey = $value->sortKeyInt;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints->fieldSettings["defaultType"];
        $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->fieldSettings["useSeconds"] ? 1 : 0;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "defaultType" => $storageDef->dataInt1,
                "useSeconds" => (bool)$storageDef->dataInt2
            )
        );

        // Building default value
        switch ( $fieldDef->fieldTypeConstraints->fieldSettings["defaultType"] )
        {
            case TimeType::DEFAULT_CURRENT_TIME:
                $dateTime = new DateTime();
                $data = $dateTime->getTimestamp() - $dateTime->setTime( 0, 0, 0 )->getTimestamp();
                break;
            default:
                $data = null;
        }

        $fieldDef->defaultValue->data = $data;
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
        return "sort_key_int";
    }
}
