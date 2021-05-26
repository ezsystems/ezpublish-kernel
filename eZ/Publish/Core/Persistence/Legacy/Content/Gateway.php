<?php

/**
 * File containing the Content Gateway base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Base class for content gateways.
 */
abstract class Gateway
{
    /**
     * Get context definition for external storage layers.
     *
     * @return array
     */
    abstract public function getContext();

    /**
     * Inserts a new content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $currentVersionNo
     *
     * @return int ID
     */
    abstract public function insertContentObject(CreateStruct $struct, $currentVersionNo = 1);

    /**
     * Inserts a new version.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     *
     * @return int ID
     */
    abstract public function insertVersion(VersionInfo $versionInfo, array $fields);

    /**
     * Updates an existing content identified by $contentId in respect to $struct.
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $struct
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $prePublishVersionInfo Provided on publish
     */
    abstract public function updateContent(
        $contentId,
        MetadataUpdateStruct $struct,
        VersionInfo $prePublishVersionInfo = null
    );

    /**
     * Updates version $versionNo for content identified by $contentId, in respect to $struct.
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $struct
     */
    abstract public function updateVersion($contentId, $versionNo, UpdateStruct $struct);

    /**
     * Updates "always available" flag for content identified by $contentId, in respect to $alwaysAvailable.
     *
     * @param int $contentId
     * @param bool $newAlwaysAvailable New "always available" value
     */
    abstract public function updateAlwaysAvailableFlag($contentId, $newAlwaysAvailable);

    /**
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $status can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED
     *
     * @param int $contentId
     * @param int $version
     * @param int $status
     *
     * @return bool
     */
    abstract public function setStatus($contentId, $version, $status);

    /**
     * Inserts a new field.
     *
     * Only used when a new field is created (i.e. a new object or a field in a
     * new language!). After that, field IDs need to stay the same, only the
     * version number changes.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     *
     * @return int ID
     */
    abstract public function insertNewField(Content $content, Field $field, StorageFieldValue $value);

    /**
     * Inserts an existing field.
     *
     * Used to insert a field with an existing ID but a new version number.
     *
     * @param Content $content
     * @param Field $field
     * @param StorageFieldValue $value
     */
    abstract public function insertExistingField(Content $content, Field $field, StorageFieldValue $value);

    /**
     * Updates an existing field.
     *
     * @param Field $field
     * @param StorageFieldValue $value
     */
    abstract public function updateField(Field $field, StorageFieldValue $value);

    /**
     * Updates an existing, non-translatable field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param int $contentId
     */
    abstract public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        $contentId
    );

    /**
     * Loads data for a content object.
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $contentId
     * @param int|null $version Current version on null value.
     * @param string[] $translations
     *
     * @return array
     */
    abstract public function load($contentId, $version = null, array $translations = null);

    /**
     * Loads current version for a list of content objects.
     *
     * @param int[] $contentIds
     * @param string[]|null $translations If languages is not set, ALL will be loaded.
     *
     * @return array[]
     */
    abstract public function loadContentList(array $contentIds, array $translations = null);

    /**
     * Loads info for a content object identified by its remote ID.
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $remoteId
     *
     * @return array
     */
    abstract public function loadContentInfoByRemoteId($remoteId);

    /**
     * Loads info for a content object identified by its location ID (node ID).
     *
     * Returns an array with the relevant data.
     *
     * @param int $locationId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return array
     */
    abstract public function loadContentInfoByLocationId($locationId);

    /**
     * Loads info for content identified by $contentId.
     * Will basically return a hash containing all field values for ezcontentobject table plus following keys:
     *  - always_available => Boolean indicating if content's language mask contains alwaysAvailable bit field
     *  - main_language_code => Language code for main (initial) language. E.g. "eng-GB".
     *
     * @param int $contentId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return array
     */
    abstract public function loadContentInfo($contentId);

    /**
     * Loads rows of info for content identified by $contentIds.
     *
     * @see loadContentInfo For the returned structure.
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadContentInfoList For how this will only return items found and not throw.
     *
     * @param array $contentIds
     *
     * @return array[]
     */
    abstract public function loadContentInfoList(array $contentIds);

    /**
     * Loads version info for content identified by $contentId and $versionNo.
     * Will basically return a hash containing all field values from ezcontentobject_version table plus following keys:
     *  - names => Hash of content object names. Key is the language code, value is the name.
     *  - languages => Hash of language ids. Key is the language code (e.g. "eng-GB"), value is the language numeric id without the always available bit.
     *  - initial_language_code => Language code for initial language in this version.
     *
     * @param int $contentId
     * @param int|null $versionNo Load current version if null.
     *
     * @return array
     */
    abstract public function loadVersionInfo($contentId, $versionNo = null);

    /**
     * Returns data for all versions with given status created by the given $userId.
     *
     * @param int $userId
     * @param int $status
     *
     * @return string[][]
     */
    abstract public function listVersionsForUser($userId, $status = VersionInfo::STATUS_DRAFT);

    /**
     * Returns all version data for the given $contentId.
     *
     * Result is returned with oldest version first (using version id as it has index and is auto increment).
     *
     * @param mixed $contentId
     * @param mixed|null $status Optional argument to filter versions by status, like {@see VersionInfo::STATUS_ARCHIVED}.
     * @param int $limit Limit for items returned, -1 means none.
     *
     * @return string[][]
     */
    abstract public function listVersions($contentId, $status = null, $limit = -1);

    /**
     * Returns all version numbers for the given $contentId.
     *
     * @param mixed $contentId
     *
     * @return int[]
     */
    abstract public function listVersionNumbers($contentId);

    /**
     * Returns last version number for content identified by $contentId.
     *
     * @param int $contentId
     *
     * @return int
     */
    abstract public function getLastVersionNumber($contentId);

    /**
     * Returns all IDs for locations that refer to $contentId.
     *
     * @param int $contentId
     *
     * @return int[]
     */
    abstract public function getAllLocationIds($contentId);

    /**
     * Returns all field IDs of $contentId grouped by their type.
     * If $versionNo is set only field IDs for that version are returned.
     * If $languageCode is set, only field IDs for that language are returned.
     *
     * @param int $contentId
     * @param int|null $versionNo
     * @param string|null $languageCode
     *
     * @return int[][]
     */
    abstract public function getFieldIdsByType($contentId, $versionNo = null, $languageCode = null);

    /**
     * Deletes relations to and from $contentId.
     * If $versionNo is set only relations for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    abstract public function deleteRelations($contentId, $versionNo = null);

    /**
     * Removes relations to Content with $contentId from Relation and RelationList field type fields.
     *
     * @param int $contentId
     */
    abstract public function removeReverseFieldRelations($contentId);

    /**
     * Deletes the field with the given $fieldId.
     *
     * @param int $fieldId
     */
    abstract public function deleteField($fieldId);

    /**
     * Deletes all fields of $contentId in all versions.
     * If $versionNo is set only fields for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    abstract public function deleteFields($contentId, $versionNo = null);

    /**
     * Deletes all versions of $contentId.
     * If $versionNo is set only that version is deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    abstract public function deleteVersions($contentId, $versionNo = null);

    /**
     * Deletes all names of $contentId.
     * If $versionNo is set only names for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    abstract public function deleteNames($contentId, $versionNo = null);

    /**
     * Sets the content object name.
     *
     * @param int $contentId
     * @param int $version
     * @param string $name
     * @param string $language
     */
    abstract public function setName($contentId, $version, $name, $language);

    /**
     * Deletes the actual content object referred to by $contentId.
     *
     * @param int $contentId
     */
    abstract public function deleteContent($contentId);

    /**
     * Loads data of related to/from $contentId.
     *
     * @param int $contentId
     * @param int $contentVersionNo
     * @param int $relationType
     *
     * @return mixed[][] Content data, array structured like {@see \eZ\Publish\Core\Persistence\Legacy\Content\Gateway::load()}
     */
    abstract public function loadRelations($contentId, $contentVersionNo = null, $relationType = null);

    /**
     * Loads data of related to/from $contentId.
     *
     * @param int $contentId
     * @param int $relationType
     *
     * @return mixed[][] Content data, array structured like {@see \eZ\Publish\Core\Persistence\Legacy\Content\Gateway::load()}
     */
    abstract public function loadReverseRelations($contentId, $relationType = null);

    /**
     * Deletes the relation with the given $relationId.
     *
     * @param int $relationId
     * @param int $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     */
    abstract public function deleteRelation($relationId, $type);

    /**
     * Inserts a new relation database record.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $createStruct
     *
     * @return int ID the inserted ID
     */
    abstract public function insertRelation(RelationCreateStruct $createStruct);

    /**
     * Returns all Content IDs for a given $contentTypeId.
     *
     * @param int $contentTypeId
     *
     * @return int[]
     */
    abstract public function getContentIdsByContentTypeId($contentTypeId);

    /**
     * Load name data for set of content id's and corresponding version number.
     *
     * @param array[] $rows array of hashes with 'id' and 'version' to load names for
     *
     * @return array
     */
    abstract public function loadVersionedNameData($rows);

    /**
     * Batch method for copying all relation meta data for copied Content object.
     *
     * Is meant to be used during content copy, so assumes the following:
     * - version number is the same
     * - content type, and hence content type attribute is the same
     * - relation type is the same
     * - target relation is the same
     *
     * @param int $originalContentId
     * @param int $copiedContentId
     * @param int|null $versionNo If specified only copy for a given version number, otherwise all.
     */
    abstract public function copyRelations($originalContentId, $copiedContentId, $versionNo = null);

    /**
     * Updates Content's attribute text value.
     *
     * @param int $attributeId
     * @param int $version
     * @param string $text
     */
    abstract public function updateContentObjectAttributeText($attributeId, $version, $text);

    /**
     * Returns an array containing all content attributes with the specified id.
     *
     * @param int $id
     *
     * @return array
     */
    abstract public function getContentObjectAttributesById($id);

    /**
     * Remove the specified translation from all the Versions of a Content Object.
     *
     * @param int $contentId
     * @param string $languageCode language code of the translation
     */
    abstract public function deleteTranslationFromContent($contentId, $languageCode);

    /**
     * Delete Content fields (attributes) for the given Translation.
     * If $versionNo is given, fields for that Version only will be deleted.
     *
     * @param string $languageCode
     * @param int $contentId
     * @param int $versionNo (optional) filter by versionNo
     */
    abstract public function deleteTranslatedFields($languageCode, $contentId, $versionNo = null);

    /**
     * Delete the specified Translation from the given Version.
     *
     * @param int $contentId
     * @param int $versionNo
     * @param string $languageCode
     */
    abstract public function deleteTranslationFromVersion($contentId, $versionNo, $languageCode);
}
