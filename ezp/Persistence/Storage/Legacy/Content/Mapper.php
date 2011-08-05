<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Content,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry;

/**
 *
 */
class Mapper
{
    /**
     * FieldValue converter registry
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $converterRegistry;

    /**
     * Creates a new mapper.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry $converterRegistry
     */
    public function __construct( Registry $converterRegistry )
    {
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * Creates a Content from the given $struct
     *
     * @param \ezp\Persistence\Content\CreateStruct $struct
     * @return Content
     */
    public function createContentFromCreateStruct( CreateStruct $struct )
    {
        $content = new Content();

        $content->name         = $struct->name;
        $content->typeId       = $struct->typeId;
        $content->sectionId    = $struct->sectionId;
        $content->ownerId      = $struct->ownerId;
        $content->versionInfos = array();

        return $content;
    }

    /**
     * Creates a new version for the given $content
     *
     * @param Content $content
     * @param int $versionNo
     * @return Version
     * @todo: created, modified, initial_language_id, status, user_id?
     */
    public function createVersionForContent( Content $content, $versionNo )
    {
        $version = new Version();

        $version->versionNo = $versionNo;
        $version->created   = time();
        $version->modified  = $version->created;
        $version->creatorId = $content->ownerId;
        // @todo: Is draft version correct?
        $version->state     = 0;
        $version->contentId = $content->id;

        return $version;
    }

    /**
     * Converts value of $field to storage value
     *
     * @param Field $field
     * @return StorageFieldValue
     */
    public function convertToStorageValue( Field $field )
    {
        $converter = $this->converterRegistry->getConverter(
            $field->type
        );
        return $converter->toStorage(
            $field->value
        );
    }

    /**
     * Extracts Content objects (and nested) from database result $rows
     *
     * Expects database rows to be indexed by keys of the format
     *
     *      "$tableName_$columnName"
     *
     * @param array $rows
     * @return array(Content)
     * @todo Take care for locations
     */
    public function extractContentFromRows( array $rows )
    {
        $contentObjs = array();
        $versions    = array();

        foreach ( $rows as $row )
        {
            $contentId = (int) $row['ezcontentobject_id'];
            if ( !isset( $contentObjs[$contentId] ) )
            {
                $contentObjs[$contentId]  = $this->extractContentFromRow( $row );
                $versions[$contentId] = array();
            }

            $versionNo = (int) $row['ezcontentobject_version_version'];
            if ( !isset( $versions[$contentId][$versionNo] ) )
            {
                $versions[$contentId][$versionNo] =
                    $this->extractVersionFromRow( $row );
            }

            $versions[$contentId][$versionNo]->fields[] =
                $this->extractFieldFromRow( $row );
        }

        foreach ( $contentObjs as $content )
        {
            $content->versionInfos = array_values( $versions[$content->id] );
        }
        return array_values( $contentObjs );
    }

    /**
     * Extracts a Content object from $row
     *
     * @param array $row
     * @return Content
     * @todo Take care for locations
     */
    protected function extractContentFromRow( array $row )
    {
        $content = new Content();

        $content->id           = (int) $row['ezcontentobject_id'];
        $content->name         = $row['ezcontentobject_name'];
        $content->typeId       = (int) $row['ezcontentobject_contentclass_id'];
        $content->sectionId    = (int) $row['ezcontentobject_section_id'];
        $content->ownerId      = (int) $row['ezcontentobject_owner_id'];
        $content->versionInfos = array();
        $content->locations    = array();

        return $content;
    }

    /**
     * Extracts a Version from the given $row
     *
     * @param array $row
     * @return Version
     */
    protected function extractVersionFromRow( array $row )
    {
        $version = new Version();

        $version->id        = (int) $row['ezcontentobject_version_id'];
        $version->versionNo = (int) $row['ezcontentobject_version_version'];
        $version->modified  = (int) $row['ezcontentobject_version_modified'];
        $version->creatorId = (int) $row['ezcontentobject_version_creator_id'];
        $version->created   = (int) $row['ezcontentobject_version_created'];
        $version->state     = (int) $row['ezcontentobject_version_status'];
        $version->contentId = (int) $row['ezcontentobject_version_contentobject_id'];
        $version->fields    = array();

        return $version;
    }

    /**
     * Extracts a Field from $row
     *
     * @param array $row
     * @return Field
     */
    protected function extractFieldFromRow( array $row )
    {
        $field = new Field();

        $field->id                = (int) $row['ezcontentobject_attribute_id'];
        $field->fieldDefinitionId = (int) $row['ezcontentobject_attribute_contentclassattribute_id'];
        $field->type              = $row['ezcontentobject_attribute_data_type_string'];
        $field->value             = $this->extractFieldValueFromRow( $row, $field->type );
        $field->language          = $row['ezcontentobject_attribute_language_code'];
        $field->versionNo         = (int) $row['ezcontentobject_attribute_version'];

        return $field;
    }

    /**
     * Extracts a FieldValue of $type from $row
     *
     * @param array $row
     * @param string $type
     * @return FieldValue
     * @throws ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Exception\NotFound
     *         if the necessary converter for $type could not be found.
     */
    protected function extractFieldValueFromRow( array $row, $type )
    {
        $storageValue = new StorageFieldValue();

        $storageValue->dataFloat     = (float) $row['ezcontentobject_attribute_data_float'];
        $storageValue->dataInt       = (int) $row['ezcontentobject_attribute_data_int'];
        $storageValue->dataText      = $row['ezcontentobject_attribute_data_text'];
        $storageValue->sortKeyInt    = (int) $row['ezcontentobject_attribute_sort_key_int'];
        $storageValue->sortKeyString = $row['ezcontentobject_attribute_sort_key_string'];

        $converter = $this->converterRegistry->getConverter( $type );
        return $converter->toFieldValue( $storageValue );
    }
}
