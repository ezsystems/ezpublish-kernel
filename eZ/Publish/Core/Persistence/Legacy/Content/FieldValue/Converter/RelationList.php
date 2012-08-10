<?php
/**
 * File containing the Relation converter
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
    eZ\Publish\Core\FieldType\RelationList\Value as RelationListValue;

class RelationList implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @static
     * @return Url
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
     *
     * @todo Implement, legacy format is xml and needs additional meta info!!
     * ref eZObjectRelationListType:contentObjectArrayXMLMap():
     *         return array( 'identifier' => 'identifier',
     *                       'priority' => 'priority',
     *                       'in-trash' => 'in_trash',
     *                       'contentobject-id' => 'contentobject_id',
     *                       'contentobject-version' => 'contentobject_version',
     *                       'node-id' => 'node_id',
     *                       'parent-node-id' => 'parent_node_id',
     *                       'contentclass-id' => 'contentclass_id',
     *                       'contentclass-identifier' => 'contentclass_identifier',
     *                       'is-modified' => 'is_modified',
     *                       'contentobject-remote-id' => 'contentobject_remote_id' );
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @todo Implement, legacy format is xml, {@see toStorageValue()}
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     *
     * @todo Implement, legacy format is xml, see RelationList\Type & eZObjectRelationListType for more info
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     *
     * @todo Implement, legacy format is xml, {@see toStorageFieldDefinition()}
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
     * @return bool
     */
    public function getIndexColumn()
    {
        return false;
    }
}
