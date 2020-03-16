<?php

/**
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
 * @internal Internal exception conversion layer.
 */
final class ExceptionConversion extends Gateway
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

    public function load(int $contentId, ?int $version = null, ?array $translations = null): array
    {
        try {
            return $this->innerGateway->load($contentId, $version, $translations);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadContentList(array $contentIds, ?array $translations = null): array
    {
        try {
            return $this->innerGateway->loadContentList($contentIds, $translations);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadContentInfoByRemoteId(string $remoteId): array
    {
        try {
            return $this->innerGateway->loadContentInfoByRemoteId($remoteId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadContentInfoByLocationId(int $locationId): array
    {
        try {
            return $this->innerGateway->loadContentInfoByLocationId($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadContentInfo(int $contentId): array
    {
        try {
            return $this->innerGateway->loadContentInfo($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadContentInfoList(array $contentIds): array
    {
        try {
            return $this->innerGateway->loadContentInfoList($contentIds);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadVersionInfo(int $contentId, ?int $versionNo = null): array
    {
        try {
            return $this->innerGateway->loadVersionInfo($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

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

    public function loadVersionsForUser(
        int $userId,
        int $status = VersionInfo::STATUS_DRAFT,
        int $offset = 0,
        int $limit = -1
    ): array {
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

    public function listReverseRelations(
        int $contentId,
        int $offset = 0,
        int $limit = -1,
        ?int $relationType = null
    ): array {
        try {
            return $this->innerGateway->listReverseRelations(
                $contentId,
                $offset,
                $limit,
                $relationType
            );
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
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

    public function deleteTranslationFromContent(int $contentId, string $languageCode): void
    {
        try {
            $this->innerGateway->deleteTranslationFromContent($contentId, $languageCode);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteTranslatedFields(
        string $languageCode,
        int $contentId,
        ?int $versionNo = null
    ): void {
        try {
            $this->innerGateway->deleteTranslatedFields(
                $languageCode,
                $contentId,
                $versionNo
            );
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteTranslationFromVersion(
        int $contentId,
        int $versionNo,
        string $languageCode
    ): void {
        try {
            $this->innerGateway->deleteTranslationFromVersion(
                $contentId,
                $versionNo,
                $languageCode
            );
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
