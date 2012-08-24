<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content;
use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Relation,
    eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry,
    eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 */
class Mapper
{
    /**
     * FieldValue converter registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $converterRegistry;

    /**
     * Location mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Caching language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * Creates a new mapper.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper $locationMapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $converterRegistry
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler $languageHandler
     */
    public function __construct( LocationMapper $locationMapper, Registry $converterRegistry, LanguageHandler $languageHandler )
    {
        $this->converterRegistry = $converterRegistry;
        $this->locationMapper = $locationMapper;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Creates a Content from the given $struct
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param int $currentVersionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function createContentFromCreateStruct( CreateStruct $struct, $currentVersionNo = 1 )
    {
        $content = new Content;
        $contentInfo = new ContentInfo;

        $contentInfo->contentTypeId = $struct->typeId;
        $contentInfo->sectionId = $struct->sectionId;
        $contentInfo->ownerId = $struct->ownerId;
        $contentInfo->alwaysAvailable = $struct->alwaysAvailable;
        $contentInfo->remoteId = $struct->remoteId;
        $contentInfo->mainLanguageCode = $this->languageHandler->load( $struct->initialLanguageId )->languageCode;
        // For drafts published and modified timestamps should be 0
        $contentInfo->publicationDate = 0;
        $contentInfo->modificationDate = 0;
        $contentInfo->currentVersionNo = $currentVersionNo;
        $contentInfo->isPublished = false;

        $content->contentInfo = $contentInfo;

        return $content;
    }

    /**
     * Creates a new version for the given $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param mixed $versionNo
     * @param array $fields
     * @param string $initialLanguageCode
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     * @todo: created, modified, initial_language_id, status, user_id?
     */
    public function createVersionInfoForContent( Content $content, $versionNo, array $fields, $initialLanguageCode, $userId )
    {
        $versionInfo = new VersionInfo;

        $versionInfo->contentId = $content->contentInfo->id;
        $versionInfo->versionNo = $versionNo;
        $versionInfo->creatorId = $userId;
        $versionInfo->status = VersionInfo::STATUS_DRAFT;
        $versionInfo->initialLanguageCode = $initialLanguageCode;
        $versionInfo->creationDate = time();
        $versionInfo->modificationDate = $versionInfo->creationDate;
        $versionInfo->names = is_object( $content->versionInfo ) ? $content->versionInfo->names : array();
        $languageCodes = array();
        foreach ( $fields as $field ) $languageCodes[] = $field->languageCode;
        foreach ( array_unique( $languageCodes ) as $languageCode )
            $versionInfo->languageIds[] =
                $this->languageHandler->loadByLanguageCode( $languageCode )->id;

        return $versionInfo;
    }

    /**
     * Converts value of $field to storage value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue
     */
    public function convertToStorageValue( Field $field )
    {
        $converter = $this->converterRegistry->getConverter(
            $field->type
        );
        $storageValue = new StorageFieldValue();
        $converter->toStorageValue(
            $field->value,
            $storageValue
        );
        return $storageValue;
    }

    /**
     * Extracts Content objects (and nested) from database result $rows
     *
     * Expects database rows to be indexed by keys of the format
     *
     *      "$tableName_$columnName"
     *
     * @param array $rows
     * @return \eZ\Publish\SPI\Persistence\Content[]
     */
    public function extractContentFromRows( array $rows )
    {
        $contentObjs = array();
        $versions = array();
        $locations = array();
        $fields = array();

        foreach ( $rows as $row )
        {
            $contentId = (int)$row['ezcontentobject_id'];
            if ( !isset( $contentObjs[$contentId] ) )
            {
                $contentObjs[$contentId] = $this->extractContentInfoFromRow( $row, 'ezcontentobject_' );
            }
            if ( !isset( $versions[$contentId] ) )
            {
                $versions[$contentId] = array();
            }
            if ( !isset( $locations[$contentId] ) )
            {
                $locations[$contentId] = array();
            }

            $versionId = (int)$row['ezcontentobject_version_id'];
            if ( !isset( $versions[$contentId][$versionId] ) )
            {
                $versions[$contentId][$versionId] = $this->extractVersionInfoFromRow( $row, 'ezcontentobject_version_' );
            }
            if ( !isset( $versions[$contentId][$versionId]->names[$row['ezcontentobject_name_content_translation']] ) )
            {
                $versions[$contentId][$versionId]->names[$row['ezcontentobject_name_content_translation']] = $row['ezcontentobject_name_name'];
            }
            if ( !isset( $locations[$contentId][$versionId] ) )
            {
                $locations[$contentId][$versionId] = array();
            }

            $fieldId = (int)$row['ezcontentobject_attribute_id'];
            if ( !isset( $fields[$contentId][$versionId][$fieldId] ) )
            {
                $fields[$contentId][$versionId][$fieldId] = $this->extractFieldFromRow( $row );
            }

            $locationId = (int)$row['ezcontentobject_tree_node_id'];
            if ( isset( $row['ezcontentobject_tree_node_id'] ) && !isset( $locations[$contentId][$versionId][$locationId] ) )
            {
                $locations[$contentId][$versionId][$locationId] =
                    $this->locationMapper->createLocationFromRow(
                        $row, 'ezcontentobject_tree_'
                    );
            }
        }

        $results = array();
        foreach ( $contentObjs as $contentId => $contentInfo )
        {
            foreach ( $versions[$contentId] as $versionId => $version )
            {
                $content = new Content;
                $content->contentInfo = $contentInfo;
                $content->versionInfo = $version;
                $content->locations = array_values( $locations[$contentId][$versionId] );
                $content->fields = array_values( $fields[$contentId][$versionId] );
                $results[] = $content;
            }
        }
        return $results;
    }

    /**
     * Extracts a ContentInfo object from $row
     *
     * @param array $row
     * @param string $prefix Prefix for row keys, which are initially mapped by ezcontentobject fields
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function extractContentInfoFromRow( array $row, $prefix = '' )
    {
        $contentInfo = new ContentInfo;
        $contentInfo->id = (int)$row["{$prefix}id"];
        $contentInfo->name = $row["{$prefix}name"];
        $contentInfo->contentTypeId = (int)$row["{$prefix}contentclass_id"];
        $contentInfo->sectionId = (int)$row["{$prefix}section_id"];
        $contentInfo->currentVersionNo = (int)$row["{$prefix}current_version"];
        $contentInfo->isPublished = (bool)( $row["{$prefix}status"] == ContentInfo::STATUS_PUBLISHED );
        $contentInfo->ownerId = (int)$row["{$prefix}owner_id"];
        $contentInfo->publicationDate = (int)$row["{$prefix}published"];
        $contentInfo->modificationDate = (int)$row["{$prefix}modified"];
        $contentInfo->alwaysAvailable = $row["{$prefix}always_available"];
        $contentInfo->mainLanguageCode = $row["{$prefix}main_language_code"];
        $contentInfo->remoteId = $row["{$prefix}remote_id"];

        return $contentInfo;
    }

    /**
     * Extracts a VersionInfo object from $row
     *
     * @param array $row
     * @param string $prefix Prefix for row keys, which are initially mapped by ezcontentobject fields
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function extractVersionInfoFromRow( array $row, $prefix = '' )
    {
        $versionInfo = new VersionInfo;
        $versionInfo->id = (int)$row["{$prefix}id"];
        $versionInfo->contentId = (int)$row["{$prefix}contentobject_id"];
        $versionInfo->versionNo = (int)$row["{$prefix}version"];
        $versionInfo->creatorId = (int)$row["{$prefix}creator_id"];
        $versionInfo->creationDate = (int)$row["{$prefix}created"];
        $versionInfo->modificationDate = (int)$row["{$prefix}modified"];
        $versionInfo->initialLanguageCode = $row["{$prefix}initial_language_code"];
        $versionInfo->languageIds = $row["{$prefix}languages"];
        $versionInfo->status = (int)$row["{$prefix}status"];
        $versionInfo->names = array();

        if ( isset( $row["{$prefix}names"] ) )
        {
            foreach ( $row["{$prefix}names"] as $name )
            {
                $versionInfo->names[$name['content_translation']] = $name['name'];
            }
        }

        return $versionInfo;
    }

    /**
     * Extracts a VersionInfo object from $row
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function extractVersionInfoListFromRows( array $rows )
    {
        $versionInfoList = array();
        foreach ( $rows as $row )
        {
            $versionId = (int)$row['ezcontentobject_version_id'];
            if ( !isset( $versionInfoList[$versionId] ) )
            {
                $versionInfo = new VersionInfo;
                $versionInfo->id = (int)$row["ezcontentobject_version_id"];
                $versionInfo->contentId = (int)$row["ezcontentobject_version_contentobject_id"];
                $versionInfo->versionNo = (int)$row["ezcontentobject_version_version"];
                $versionInfo->creatorId = (int)$row["ezcontentobject_version_creator_id"];
                $versionInfo->creationDate = (int)$row["ezcontentobject_version_created"];
                $versionInfo->modificationDate = (int)$row["ezcontentobject_version_modified"];
                $versionInfo->initialLanguageCode = $this->languageHandler->load( $row["ezcontentobject_version_initial_language_id"] )->languageCode;
                $versionInfo->languageIds = $this->extractLanguageIdsFromMask( (int)$row["ezcontentobject_version_language_mask"] );//array();
                $versionInfo->status = (int)$row["ezcontentobject_version_status"];
                $versionInfo->names = array();
                $versionInfoList[$versionId] = $versionInfo;
            }

            if ( !isset( $versionInfoList[$versionId]->names[$row['ezcontentobject_name_content_translation']] ) )
            {
                $versionInfoList[$versionId]->names[$row['ezcontentobject_name_content_translation']] = $row['ezcontentobject_name_name'];
            }
        }
        return array_values( $versionInfoList );
    }

    /**
     * @todo use langmask handler for this
     *
     * @param $languageMask
     *
     * @return array
     */
    public function extractLanguageIdsFromMask( $languageMask )
    {
        $exp = 2;
        $result = array();

        // Decomposition of $languageMask into its binary components.
        while ( $exp <= $languageMask )
        {
            if ( $languageMask & $exp )
                $result[] = $exp;

            $exp *= 2;
        }

        return $result;
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

        $field->id = (int)$row['ezcontentobject_attribute_id'];
        $field->fieldDefinitionId = (int)$row['ezcontentobject_attribute_contentclassattribute_id'];
        $field->type = $row['ezcontentobject_attribute_data_type_string'];
        $field->value = $this->extractFieldValueFromRow( $row, $field->type );
        $field->languageCode = $row['ezcontentobject_attribute_language_code'];
        $field->versionNo = (int)$row['ezcontentobject_attribute_version'];

        return $field;
    }

    /**
     * Extracts a FieldValue of $type from $row
     *
     * @param array $row
     * @param string $type
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     *         if the necessary converter for $type could not be found.
     */
    protected function extractFieldValueFromRow( array $row, $type )
    {
        $storageValue = new StorageFieldValue();

        // Nullable field
        $storageValue->dataFloat = isset( $row['ezcontentobject_attribute_data_float'] )
            ? (float)$row['ezcontentobject_attribute_data_float']
            : null;
        // Nullable field
        $storageValue->dataInt = isset( $row['ezcontentobject_attribute_data_int'] )
            ? (int)$row['ezcontentobject_attribute_data_int']
            : null;
        $storageValue->dataText = $row['ezcontentobject_attribute_data_text'];
        // Not nullable field
        $storageValue->sortKeyInt = (int)$row['ezcontentobject_attribute_sort_key_int'];
        $storageValue->sortKeyString = $row['ezcontentobject_attribute_sort_key_string'];

        $fieldValue = new FieldValue();

        $converter = $this->converterRegistry->getConverter( $type );
        $converter->toFieldValue( $storageValue, $fieldValue );

        return $fieldValue;
    }

    /**
     * Creates CreateStruct from $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    public function createCreateStructFromContent( Content $content )
    {
        $struct = new CreateStruct();
        $struct->name = $content->versionInfo->names;
        $struct->typeId = $content->contentInfo->contentTypeId;
        $struct->sectionId = $content->contentInfo->sectionId;
        $struct->ownerId = $content->contentInfo->ownerId;
        $struct->locations = array();
        $struct->alwaysAvailable = $content->contentInfo->alwaysAvailable;
        $struct->remoteId = md5( uniqid( get_class( $this ), true ) );
        $struct->initialLanguageId = $this->languageHandler->loadByLanguageCode( $content->versionInfo->initialLanguageCode )->id;
        $struct->modified = time();

        foreach ( $content->fields as $field )
        {
            $newField = clone $field;
            $newField->id = null;
            $struct->fields[] = $newField;
        }

        return $struct;
    }

    /**
     * Extracts relation objects from $rows
     */
    public function extractRelationsFromRows( array $rows )
    {
        $relations = array();

        foreach ( $rows as $row )
        {
            $id = (int)$row['ezcontentobject_link_id'];
            if ( !isset( $relations[$id] ) )
            {
                $relations[$id] = $this->extractRelationFromRow( $row );
            }
        }

        return $relations;
    }

    /**
     * Extracts a Relation object from a $row
     *
     * @param array $row Associative array representing a relation
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    protected function extractRelationFromRow( array $row )
    {
        $relation = new Relation();
        $relation->id = (int)$row['ezcontentobject_link_id'];
        $relation->sourceContentId = (int)$row['ezcontentobject_link_from_contentobject_id'];
        $relation->sourceContentVersionNo = (int)$row['ezcontentobject_link_from_contentobject_version'];
        $relation->destinationContentId = (int)$row['ezcontentobject_link_to_contentobject_id'];
        $relation->type = (int)$row['ezcontentobject_link_relation_type'];

        return $relation;
    }

    /**
     * Creates a Content from the given $struct
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    public function createRelationFromCreateStruct( RelationCreateStruct $struct )
    {
        $relation = new Relation();

        $relation->destinationContentId = $struct->destinationContentId;
        $relation->sourceContentId = $struct->sourceContentId;
        $relation->sourceContentVersionNo = $struct->sourceContentVersionNo;
        $relation->sourceFieldDefinitionId = $struct->sourceFieldDefinitionId;
        $relation->type = $struct->type;

        return $relation;
    }
}
