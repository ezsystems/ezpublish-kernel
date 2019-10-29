<?php

/**
 * File containing the Content Gateway base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
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

/**
 * Base class for content gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function insertContentObject(CreateStruct $struct, int $currentVersionNo = 1): int
    {
        try {
            return $this->innerGateway->insertContentObject($struct, $currentVersionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertVersion(VersionInfo $versionInfo, array $fields): int
    {
        try {
            return $this->innerGateway->insertVersion($versionInfo, $fields);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateContent(
        int $contentId,
        MetadataUpdateStruct $struct,
        ?VersionInfo $prePublishVersionInfo = null
    ): void {
        try {
            $this->innerGateway->updateContent($contentId, $struct, $prePublishVersionInfo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Updates version $versionNo for content identified by $contentId, in respect to $struct.
     */
    public function updateVersion(int $contentId, int $versionNo, UpdateStruct $struct): void
    {
        try {
            $this->innerGateway->updateVersion($contentId, $versionNo, $struct);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateAlwaysAvailableFlag(
        int $contentId,
        ?bool $newAlwaysAvailable = null
    ): void {
        try {
            $this->innerGateway->updateAlwaysAvailableFlag($contentId, $newAlwaysAvailable);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setStatus(int $contentId, int $version, int $status): bool
    {
        try {
            return $this->innerGateway->setStatus($contentId, $version, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setPublishedStatus(int $contentId, int $status): void
    {
        try {
            $this->innerGateway->setPublishedStatus($contentId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertNewField(Content $content, Field $field, StorageFieldValue $value): int
    {
        try {
            return $this->innerGateway->insertNewField($content, $field, $value);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertExistingField(
        Content $content,
        Field $field,
        StorageFieldValue $value
    ): void {
        try {
            $this->innerGateway->insertExistingField($content, $field, $value);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateField(Field $field, StorageFieldValue $value): void
    {
        try {
            $this->innerGateway->updateField($field, $value);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        int $contentId
    ): void {
        try {
            $this->innerGateway->updateNonTranslatableField($field, $value, $contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($contentId, $version = null, array $translations = null)
    {
        try {
            return $this->innerGateway->load($contentId, $version, $translations);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentList(array $contentIds, array $translations = null): array
    {
        try {
            return $this->innerGateway->loadContentList($contentIds, $translations);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadContentInfoList(array $contentIds)
    {
        try {
            return $this->innerGateway->loadContentInfoList($contentIds);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Returns the number of all versions with given status created by the given $userId.
     *
     * @param int $userId
     * @param int $status
     *
     * @return int
     */
    public function countVersionsForUser(int $userId, int $status = VersionInfo::STATUS_DRAFT): int
    {
        try {
            return $this->innerGateway->countVersionsForUser($userId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * @return string[][]
     */
    public function listVersionsForUser(int $userId, int $status = VersionInfo::STATUS_DRAFT): array
    {
        try {
            return $this->innerGateway->listVersionsForUser($userId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadVersionsForUser($userId, $status = VersionInfo::STATUS_DRAFT, int $offset = 0, int $limit = -1): array
    {
        try {
            return $this->innerGateway->loadVersionsForUser($userId, $status, $offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function listVersions(int $contentId, ?int $status = null, int $limit = -1): array
    {
        try {
            return $this->innerGateway->listVersions($contentId, $status, $limit);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function listVersionNumbers(int $contentId): array
    {
        try {
            return $this->innerGateway->listVersionNumbers($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Returns last version number for content identified by $contentId.
     *
     * @param int $contentId
     *
     * @return int
     */
    public function getLastVersionNumber(int $contentId): int
    {
        try {
            return $this->innerGateway->getLastVersionNumber($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getAllLocationIds(int $contentId): array
    {
        try {
            return $this->innerGateway->getAllLocationIds($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getFieldIdsByType(
        int $contentId,
        ?int $versionNo = null,
        ?string $languageCode = null
    ): array {
        try {
            return $this->innerGateway->getFieldIdsByType($contentId, $versionNo, $languageCode);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteRelations(int $contentId, ?int $versionNo = null): void
    {
        try {
            $this->innerGateway->deleteRelations($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeReverseFieldRelations(int $contentId): void
    {
        try {
            $this->innerGateway->removeReverseFieldRelations($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteField(int $fieldId): void
    {
        try {
            $this->innerGateway->deleteField($fieldId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteFields(int $contentId, ?int $versionNo = null): void
    {
        try {
            $this->innerGateway->deleteFields($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteVersions(int $contentId, ?int $versionNo = null): void
    {
        try {
            $this->innerGateway->deleteVersions($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteNames(int $contentId, ?int $versionNo = null): void
    {
        try {
            $this->innerGateway->deleteNames($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setName(int $contentId, int $version, string $name, string $languageCode): void
    {
        try {
            $this->innerGateway->setName($contentId, $version, $name, $languageCode);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteContent(int $contentId): void
    {
        try {
            $this->innerGateway->deleteContent($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadRelations(
        int $contentId,
        ?int $contentVersionNo = null,
        ?int $relationType = null
    ): array {
        try {
            return $this->innerGateway->loadRelations($contentId, $contentVersionNo, $relationType);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countReverseRelations(int $contentId, ?int $relationType = null): int
    {
        try {
            return $this->innerGateway->countReverseRelations($contentId, $relationType);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadReverseRelations(int $contentId, ?int $relationType = null): array
    {
        try {
            return $this->innerGateway->loadReverseRelations($contentId, $relationType);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listReverseRelations(int $contentId, int $offset = 0, int $limit = -1, ?int $relationType = null): array
    {
        try {
            return $this->innerGateway->listReverseRelations($contentId, $offset, $limit, $relationType);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function deleteRelation(int $relationId, int $type): void
    {
        try {
            $this->innerGateway->deleteRelation($relationId, $type);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertRelation(RelationCreateStruct $struct): int
    {
        try {
            return $this->innerGateway->insertRelation($struct);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getContentIdsByContentTypeId($contentTypeId): array
    {
        try {
            return $this->innerGateway->getContentIdsByContentTypeId($contentTypeId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadVersionedNameData(array $rows): array
    {
        try {
            return $this->innerGateway->loadVersionedNameData($rows);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function copyRelations(
        int $originalContentId,
        int $copiedContentId,
        ?int $versionNo = null
    ): void {
        try {
            $this->innerGateway->copyRelations($originalContentId, $copiedContentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
