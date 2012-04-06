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
    eZ\Publish\SPI\Persistence\Content\Version,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler as LanguageHandler,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 */
class Mapper
{
    /**
     * FieldValue converter registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry
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
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry $converterRegistry
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
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function createContentFromCreateStruct( CreateStruct $struct )
    {
        $content = new Content;
        $contentInfo = new ContentInfo;

        $contentInfo->contentTypeId = $struct->typeId;
        $contentInfo->sectionId = $struct->sectionId;
        $contentInfo->ownerId = $struct->ownerId;
        $contentInfo->isAlwaysAvailable = $struct->alwaysAvailable;
        $contentInfo->remoteId = $struct->remoteId;
        $contentInfo->mainLanguageCode = $this->languageHandler->getById( $struct->initialLanguageId )->languageCode;
        // For drafts published and modified timestamps should be 0
        $contentInfo->publicationDate = 0;
        $contentInfo->modificationDate = 0;
        $contentInfo->currentVersionNo = 1;
        $contentInfo->isPublished = false;

        $content->contentInfo = $contentInfo;

        return $content;
    }

    /**
     * Creates a new version for the given $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param int $versionNo
     * @param array $fields
     * @param string $initialLanguageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     * @todo: created, modified, initial_language_id, status, user_id?
     */
    public function createVersionInfoForContent( Content $content, $versionNo, array $fields, $initialLanguageCode )
    {
        $versionInfo = new VersionInfo;

        $versionInfo->contentId = $content->contentInfo->contentId;
        $versionInfo->versionNo = $versionNo;
        $versionInfo->creatorId = $content->contentInfo->ownerId;
        $versionInfo->status = VersionInfo::STATUS_DRAFT;
        $versionInfo->initialLanguageCode = $initialLanguageCode;
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
            if ( !isset( $locations[$contentId][$versionId][$locationId] ) )
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
        $contentInfo->contentId = (int)$row["{$prefix}id"];
        $contentInfo->name = $row["{$prefix}name"];
        $contentInfo->contentTypeId = (int)$row["{$prefix}contentclass_id"];
        $contentInfo->sectionId = (int)$row["{$prefix}section_id"];
        $contentInfo->currentVersionNo = (int)$row["{$prefix}current_version"];
        $contentInfo->isPublished = (bool)( $row["{$prefix}status"] == ContentInfo::STATUS_PUBLISHED );
        $contentInfo->ownerId = (int)$row["{$prefix}owner_id"];
        $contentInfo->publicationDate = (int)$row["{$prefix}published"];
        $contentInfo->modificationDate = (int)$row["{$prefix}modified"];
        $contentInfo->isAlwaysAvailable = $row["{$prefix}always_available"];
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
                $versionInfo->initialLanguageCode = $this->languageHandler->getById( $row["ezcontentobject_version_initial_language_id"] )->languageCode;
                $versionInfo->languageIds = array();
                $versionInfo->status = (int)$row["ezcontentobject_version_status"];
                $versionInfo->names = array();
                $versionInfoList[$versionId] = $versionInfo;
            }

            if ( !isset( $versionInfoList[$versionId]->names[$row['ezcontentobject_name_content_translation']] ) )
            {
                $versionInfoList[$versionId]->names[$row['ezcontentobject_name_content_translation']] = $row['ezcontentobject_name_name'];
            }

            if (
                !in_array(
                    $row['ezcontentobject_attribute_language_id'] & ~1,// lang_id can include always available flag, eg:
                    $versionInfoList[$versionId]->languageIds          // eng-US can be either 2 or 3, see fixture data
                )
            )
            {
                $versionInfoList[$versionId]->languageIds[] =
                    $row['ezcontentobject_attribute_language_id'] & ~1;
            }
        }
        return array_values( $versionInfoList );
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

        $storageValue->dataFloat = (float)$row['ezcontentobject_attribute_data_float'];
        $storageValue->dataInt = (int)$row['ezcontentobject_attribute_data_int'];
        $storageValue->dataText = $row['ezcontentobject_attribute_data_text'];
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
        $struct->alwaysAvailable = $content->contentInfo->isAlwaysAvailable;
        $struct->remoteId = $content->contentInfo->remoteId;
        $struct->initialLanguageId = $this->languageHandler->getByLocale( $content->versionInfo->initialLanguageCode )->id;
        $struct->modified = $content->contentInfo->modificationDate;

        foreach ( $content->fields as $field )
        {
            $newField = clone $field;
            $newField->id = null;
            $struct->fields[] = $newField;
        }

        return $struct;
    }
}
