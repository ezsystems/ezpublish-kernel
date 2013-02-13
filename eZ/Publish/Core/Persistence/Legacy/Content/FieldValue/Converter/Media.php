<?php
/**
 * File containing the Image converter
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Media\Type as MediaType;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\FieldType\FieldSettings;

class Media extends BinaryFile
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @return Image
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        parent::toStorageFieldDefinition( $fieldDef, $storageDef );

        $storageDef->dataText1 = ( isset( $fieldDef->fieldTypeConstraints->fieldSettings['mediaType'] )
            ? $fieldDef->fieldTypeConstraints->fieldSettings['mediaType']
            : MediaType::TYPE_HTML5_VIDEO );
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        parent::toFieldDefinition( $storageDef, $fieldDef );
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'mediaType' => $storageDef->dataText1,
            )
        );
    }
}
