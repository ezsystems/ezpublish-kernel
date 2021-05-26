<?php

/**
 * File containing the Content Gateway base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use Doctrine\DBAL\DBALException;
use PDOException;
use RuntimeException;

/**
 * Base class for content gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Get context definition for external storage layers.
     *
     * @return array
     */
    public function getContext()
    {
        try {
            return $this->innerGateway->getContext();
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Inserts a new content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $currentVersionNo
     *
     * @return int ID
     */
    public function insertContentObject(CreateStruct $struct, $currentVersionNo = 1)
    {
        try {
            return $this->innerGateway->insertContentObject($struct, $currentVersionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Inserts a new version.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     *
     * @return int ID
     */
    public function insertVersion(VersionInfo $versionInfo, array $fields)
    {
        try {
            return $this->innerGateway->insertVersion($versionInfo, $fields);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Updates an existing content identified by $contentId in respect to $struct.
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $struct
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $prePublishVersionInfo Provided on publish
     */
    public function updateContent($contentId, MetadataUpdateStruct $struct, VersionInfo $prePublishVersionInfo = null)
    {
        try {
            return $this->innerGateway->updateContent($contentId, $struct, $prePublishVersionInfo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Updates version $versionNo for content identified by $contentId, in respect to $struct.
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $struct
     */
    public function updateVersion($contentId, $versionNo, UpdateStruct $struct)
    {
        try {
            return $this->innerGateway->updateVersion($contentId, $versionNo, $struct);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Updates "always available" flag for content identified by $contentId, in respect to $alwaysAvailable.
     *
     * @param int $contentId
     * @param bool $newAlwaysAvailable New "always available" value
     */
    public function updateAlwaysAvailableFlag($contentId, $newAlwaysAvailable)
    {
        try {
            return $this->innerGateway->updateAlwaysAvailableFlag($contentId, $newAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

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
    public function setStatus($contentId, $version, $status)
    {
        try {
            return $this->innerGateway->setStatus($contentId, $version, $status);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

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
    public function insertNewField(Content $content, Field $field, StorageFieldValue $value)
    {
        try {
            return $this->innerGateway->insertNewField($content, $field, $value);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Inserts an existing field.
     *
     * Used to insert a field with an exsting ID but a new version number.
     *
     * @param Content $content
     * @param Field $field
     * @param StorageFieldValue $value
     */
    public function insertExistingField(Content $content, Field $field, StorageFieldValue $value)
    {
        try {
            return $this->innerGateway->insertExistingField($content, $field, $value);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Updates an existing field.
     *
     * @param Field $field
     * @param StorageFieldValue $value
     */
    public function updateField(Field $field, StorageFieldValue $value)
    {
        try {
            return $this->innerGateway->updateField($field, $value);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Updates an existing, non-translatable field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param int $contentId
     */
    public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        $contentId
    ) {
        try {
            return $this->innerGateway->updateNonTranslatableField($field, $value, $contentId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($contentId, $version = null, array $translations = null)
    {
        try {
            return $this->innerGateway->load($contentId, $version, $translations);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentList(array $contentIds, array $translations = null)
    {
        try {
            return $this->innerGateway->loadContentList($contentIds, $translations);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads data for a content object identified by its remote ID.
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $remoteId
     *
     * @return array
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        try {
            return $this->innerGateway->loadContentInfoByRemoteId($remoteId);
        } catch (DBALException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        }
    }

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
    public function loadContentInfoByLocationId($locationId)
    {
        try {
            return $this->innerGateway->loadContentInfoByLocationId($locationId);
        } catch (DBALException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        }
    }

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
    public function loadContentInfo($contentId)
    {
        try {
            return $this->innerGateway->loadContentInfo($contentId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function loadContentInfoList(array $contentIds)
    {
        try {
            return $this->innerGateway->loadContentInfoList($contentIds);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads version info for content identified by $contentId and $versionNo.
     * Will basically return a hash containing all field values from ezcontentobject_version table plus following keys:
     *  - names => Hash of content object names. Key is the language code, value is the name.
     *  - languages => Hash of language ids. Key is the language code (e.g. "eng-GB"), value is the language numeric id without the always available bit.
     *  - initial_language_code => Language code for initial language in this version.
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return array
     */
    public function loadVersionInfo($contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->loadVersionInfo($contentId, $versionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Returns data for all versions with given status created by the given $userId.
     *
     * @param int $userId
     * @param int $status
     *
     * @return string[][]
     */
    public function listVersionsForUser($userId, $status = VersionInfo::STATUS_DRAFT)
    {
        try {
            return $this->innerGateway->listVersionsForUser($userId, $status);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

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
    public function listVersions($contentId, $status = null, $limit = -1)
    {
        try {
            return $this->innerGateway->listVersions($contentId, $status, $limit);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Returns all version numbers for the given $contentId.
     *
     * @param mixed $contentId
     *
     * @return int[]
     */
    public function listVersionNumbers($contentId)
    {
        try {
            return $this->innerGateway->listVersionNumbers($contentId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Returns last version number for content identified by $contentId.
     *
     * @param int $contentId
     *
     * @return int
     */
    public function getLastVersionNumber($contentId)
    {
        try {
            return $this->innerGateway->getLastVersionNumber($contentId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Returns all IDs for locations that refer to $contentId.
     *
     * @param int $contentId
     *
     * @return int[]
     */
    public function getAllLocationIds($contentId)
    {
        try {
            return $this->innerGateway->getAllLocationIds($contentId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

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
    public function getFieldIdsByType($contentId, $versionNo = null, $languageCode = null)
    {
        try {
            return $this->innerGateway->getFieldIdsByType($contentId, $versionNo, $languageCode);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes relations to and from $contentId.
     * If $versionNo is set only relations for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteRelations($contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->deleteRelations($contentId, $versionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Removes relations to Content with $contentId from Relation and RelationList field type fields.
     *
     * @param int $contentId
     */
    public function removeReverseFieldRelations($contentId)
    {
        try {
            return $this->innerGateway->removeReverseFieldRelations($contentId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes the field with the given $fieldId.
     *
     * @param int $fieldId
     */
    public function deleteField($fieldId)
    {
        try {
            return $this->innerGateway->deleteField($fieldId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes all fields of $contentId in all versions.
     * If $versionNo is set only fields for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteFields($contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->deleteFields($contentId, $versionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes all versions of $contentId.
     * If $versionNo is set only that version is deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteVersions($contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->deleteVersions($contentId, $versionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes all names of $contentId.
     * If $versionNo is set only names for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function deleteNames($contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->deleteNames($contentId, $versionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Sets the content object name.
     *
     * @param int $contentId
     * @param int $version
     * @param string $name
     * @param string $language
     */
    public function setName($contentId, $version, $name, $language)
    {
        try {
            return $this->innerGateway->setName($contentId, $version, $name, $language);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes the actual content object referred to by $contentId.
     *
     * @param int $contentId
     */
    public function deleteContent($contentId)
    {
        try {
            return $this->innerGateway->deleteContent($contentId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads data of related to/from $contentId.
     *
     * @param int $contentId
     * @param int $contentVersionNo
     * @param int $relationType
     *
     * @return mixed[][] Content data, array structured like {@see \eZ\Publish\Core\Persistence\Legacy\Content\Gateway::load()}
     */
    public function loadRelations($contentId, $contentVersionNo = null, $relationType = null)
    {
        try {
            return $this->innerGateway->loadRelations($contentId, $contentVersionNo, $relationType);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads data of related to/from $contentId.
     *
     * @param int $contentId
     * @param int $relationType
     *
     * @return mixed[][] Content data, array structured like {@see \eZ\Publish\Core\Persistence\Legacy\Content\Gateway::load()}
     */
    public function loadReverseRelations($contentId, $relationType = null)
    {
        try {
            return $this->innerGateway->loadReverseRelations($contentId, $relationType);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes the relation with the given $relationId.
     *
     * @param int $relationId
     * @param int $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     */
    public function deleteRelation($relationId, $type)
    {
        try {
            return $this->innerGateway->deleteRelation($relationId, $type);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Inserts a new relation database record.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $struct
     *
     * @return int ID the inserted ID
     */
    public function insertRelation(RelationCreateStruct $struct)
    {
        try {
            return $this->innerGateway->insertRelation($struct);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Returns all Content IDs for a given $contentTypeId.
     *
     * @param int $contentTypeId
     *
     * @return int[]
     */
    public function getContentIdsByContentTypeId($contentTypeId)
    {
        try {
            return $this->innerGateway->getContentIdsByContentTypeId($contentTypeId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Load name data for set of content id's and corresponding version number.
     *
     * @param array[] $rows array of hashes with 'id' and 'version' to load names for
     *
     * @return array
     */
    public function loadVersionedNameData($rows)
    {
        try {
            return $this->innerGateway->loadVersionedNameData($rows);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Batch method for copying all relation meta data for copied Content object.
     *
     * {@inheritdoc}
     *
     * @param int $originalContentId
     * @param int $copiedContentId
     * @param int|null $versionNo If specified only copy for a given version number, otherwise all.
     */
    public function copyRelations($originalContentId, $copiedContentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->copyRelations($originalContentId, $copiedContentId, $versionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Remove the specified translation from all the Versions of a Content Object.
     *
     * @param int $contentId
     * @param string $languageCode language code of the translation
     */
    public function deleteTranslationFromContent($contentId, $languageCode)
    {
        try {
            return $this->innerGateway->deleteTranslationFromContent($contentId, $languageCode);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Delete Content fields (attributes) for the given Translation.
     * If $versionNo is given, fields for that Version only will be deleted.
     *
     * @param string $languageCode
     * @param int $contentId
     * @param int $versionNo (optional) filter by versionNo
     */
    public function deleteTranslatedFields($languageCode, $contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->deleteTranslatedFields($languageCode, $contentId, $versionNo);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Delete the specified Translation from the given Version.
     *
     * @param int $contentId
     * @param int $versionNo
     * @param string $languageCode
     */
    public function deleteTranslationFromVersion($contentId, $versionNo, $languageCode)
    {
        try {
            return $this->innerGateway->deleteTranslationFromVersion($contentId, $versionNo, $languageCode);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Updates Content's attribute text value.
     *
     * @param int $attributeId
     * @param int $version
     * @param string $text
     */
    public function updateContentObjectAttributeText($attributeId, $version, $text)
    {
        try {
            $this->innerGateway->updateContentObjectAttributeText($attributeId, $version, $text);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Returns an array containing all content attributes with the specified id.
     *
     * @param int $id
     *
     * @return array
     */
    public function getContentObjectAttributesById($id)
    {
        try {
            return $this->innerGateway->getContentObjectAttributesById($id);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
