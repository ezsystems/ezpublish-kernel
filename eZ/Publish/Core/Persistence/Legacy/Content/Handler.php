<?php

/**
 * File containing the Content Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content;

use Exception;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Handler as BaseContentHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway as UrlAliasGateway;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * The Content Handler stores Content and ContentType objects.
 */
class Handler implements BaseContentHandler
{
    /**
     * Content gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Location gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Mapper.
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * FieldHandler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected $fieldHandler;

    /**
     * URL slug converter.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
     */
    protected $slugConverter;

    /**
     * UrlAlias gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $urlAliasGateway;

    /**
     * ContentType handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Tree handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler
     */
    protected $treeHandler;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler $fieldHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter $slugConverter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $urlAliasGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler $treeHandler
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        Gateway $contentGateway,
        LocationGateway $locationGateway,
        Mapper $mapper,
        FieldHandler $fieldHandler,
        SlugConverter $slugConverter,
        UrlAliasGateway $urlAliasGateway,
        ContentTypeHandler $contentTypeHandler,
        TreeHandler $treeHandler,
        LoggerInterface $logger = null
    ) {
        $this->contentGateway = $contentGateway;
        $this->locationGateway = $locationGateway;
        $this->mapper = $mapper;
        $this->fieldHandler = $fieldHandler;
        $this->slugConverter = $slugConverter;
        $this->urlAliasGateway = $urlAliasGateway;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->treeHandler = $treeHandler;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * Creates a new Content entity in the storage engine.
     *
     * The values contained inside the $content will form the basis of stored
     * entity.
     *
     * Will contain always a complete list of fields.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct Content creation struct.
     *
     * @return \eZ\Publish\SPI\Persistence\Content Content value object
     */
    public function create(CreateStruct $struct)
    {
        return $this->internalCreate($struct);
    }

    /**
     * Creates a new Content entity in the storage engine.
     *
     * The values contained inside the $content will form the basis of stored
     * entity.
     *
     * Will contain always a complete list of fields.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct Content creation struct.
     * @param mixed $versionNo Used by self::copy() to maintain version numbers
     *
     * @return \eZ\Publish\SPI\Persistence\Content Content value object
     */
    protected function internalCreate(CreateStruct $struct, $versionNo = 1)
    {
        $content = new Content();

        $content->fields = $struct->fields;
        $content->versionInfo = $this->mapper->createVersionInfoFromCreateStruct($struct, $versionNo);

        $content->versionInfo->contentInfo->id = $this->contentGateway->insertContentObject($struct, $versionNo);
        $content->versionInfo->id = $this->contentGateway->insertVersion(
            $content->versionInfo,
            $struct->fields
        );

        $contentType = $this->contentTypeHandler->load($struct->typeId);
        $this->fieldHandler->createNewFields($content, $contentType);

        // Create node assignments
        foreach ($struct->locations as $location) {
            $location->contentId = $content->versionInfo->contentInfo->id;
            $location->contentVersion = $content->versionInfo->versionNo;
            $this->locationGateway->createNodeAssignment(
                $location,
                $location->parentId,
                LocationGateway::NODE_ASSIGNMENT_OP_CODE_CREATE
            );
        }

        // Create names
        foreach ($content->versionInfo->names as $language => $name) {
            $this->contentGateway->setName(
                $content->versionInfo->contentInfo->id,
                $content->versionInfo->versionNo,
                $name,
                $language
            );
        }

        return $content;
    }

    /**
     * Performs the publishing operations required to set the version identified by $updateStruct->versionNo and
     * $updateStruct->id as the published one.
     *
     * The publish procedure will:
     * - Create location nodes based on the node assignments
     * - Update the content object using the provided metadata update struct
     * - Update the node assignments
     * - Update location nodes of the content with the new published version
     * - Set content and version status to published
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $metaDataUpdateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content The published Content
     */
    public function publish($contentId, $versionNo, MetadataUpdateStruct $metaDataUpdateStruct)
    {
        // Archive currently published version
        $versionInfo = $this->loadVersionInfo($contentId, $versionNo);
        if ($versionInfo->contentInfo->currentVersionNo != $versionNo) {
            $this->setStatus(
                $contentId,
                VersionInfo::STATUS_ARCHIVED,
                $versionInfo->contentInfo->currentVersionNo
            );
        }

        // Set always available name for the content
        $metaDataUpdateStruct->name = $versionInfo->names[$versionInfo->contentInfo->mainLanguageCode];

        $this->contentGateway->updateContent($contentId, $metaDataUpdateStruct, $versionInfo);
        $this->locationGateway->createLocationsFromNodeAssignments(
            $contentId,
            $versionNo
        );

        $this->locationGateway->updateLocationsContentVersionNo($contentId, $versionNo);
        $this->setStatus($contentId, VersionInfo::STATUS_PUBLISHED, $versionNo);

        return $this->load($contentId, $versionNo);
    }

    /**
     * Creates a new draft version from $contentId in $version.
     *
     * Copies all fields from $contentId in $srcVersion and creates a new
     * version of the referred Content from it.
     *
     * Note: When creating a new draft in the old admin interface there will
     * also be an entry in the `eznode_assignment` created for the draft. This
     * is ignored in this implementation.
     *
     * @param mixed $contentId
     * @param mixed $srcVersion
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function createDraftFromVersion($contentId, $srcVersion, $userId)
    {
        $content = $this->load($contentId, $srcVersion);

        // Create new version
        $content->versionInfo = $this->mapper->createVersionInfoForContent(
            $content,
            $this->contentGateway->getLastVersionNumber($contentId) + 1,
            $userId
        );
        $content->versionInfo->id = $this->contentGateway->insertVersion(
            $content->versionInfo,
            $content->fields
        );

        // Clone fields from previous version and append them to the new one
        $this->fieldHandler->createExistingFieldsInNewVersion($content);

        // Create relations for new version
        $relations = $this->contentGateway->loadRelations($contentId, $srcVersion);
        foreach ($relations as $relation) {
            $this->contentGateway->insertRelation(
                new RelationCreateStruct(
                    [
                        'sourceContentId' => $contentId,
                        'sourceContentVersionNo' => $content->versionInfo->versionNo,
                        'sourceFieldDefinitionId' => $relation['ezcontentobject_link_contentclassattribute_id'],
                        'destinationContentId' => $relation['ezcontentobject_link_to_contentobject_id'],
                        'type' => (int)$relation['ezcontentobject_link_relation_type'],
                    ]
                )
            );
        }

        // Create content names for new version
        foreach ($content->versionInfo->names as $language => $name) {
            $this->contentGateway->setName(
                $contentId,
                $content->versionInfo->versionNo,
                $name,
                $language
            );
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id, $version = null, array $translations = null)
    {
        $rows = $this->contentGateway->load($id, $version, $translations);

        if (empty($rows)) {
            throw new NotFound('content', "contentId: $id, versionNo: $version");
        }

        $contentObjects = $this->mapper->extractContentFromRows(
            $rows,
            $this->contentGateway->loadVersionedNameData([[
                'id' => $id,
                'version' => $rows[0]['ezcontentobject_version_version'],
            ]])
        );
        $content = $contentObjects[0];
        unset($rows, $contentObjects);

        $this->fieldHandler->loadExternalFieldData($content);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentList(array $contentIds, array $translations = null): array
    {
        $rawList = $this->contentGateway->loadContentList($contentIds, $translations);
        if (empty($rawList)) {
            return [];
        }

        $idVersionPairs = [];
        foreach ($rawList as $row) {
            // As there is only one version per id, set id as key to avoid duplicates
            $idVersionPairs[$row['ezcontentobject_id']] = [
                'id' => $row['ezcontentobject_id'],
                'version' => $row['ezcontentobject_version_version'],
            ];
        }

        // group name data per Content Id
        $nameData = $this->contentGateway->loadVersionedNameData(array_values($idVersionPairs));
        $contentItemNameData = [];
        foreach ($nameData as $nameDataRow) {
            $contentId = $nameDataRow['ezcontentobject_name_contentobject_id'];
            $contentItemNameData[$contentId][] = $nameDataRow;
        }

        // group rows per Content Id be able to ignore Content items with erroneous data
        $contentItemsRows = [];
        foreach ($rawList as $row) {
            $contentId = $row['ezcontentobject_id'];
            $contentItemsRows[$contentId][] = $row;
        }
        unset($rawList, $idVersionPairs);

        // try to extract Content from each Content data
        $contentItems = [];
        foreach ($contentItemsRows as $contentId => $contentItemsRow) {
            try {
                $contentList = $this->mapper->extractContentFromRows(
                    $contentItemsRow,
                    $contentItemNameData[$contentId]
                );
                $contentItems[$contentId] = $contentList[0];
            } catch (Exception $e) {
                $this->logger->warning(
                    sprintf(
                        '%s: Content %d not loaded: %s',
                        __METHOD__,
                        $contentId,
                        $e->getMessage()
                    )
                );
            }
        }

        // try to load External Storage data for each Content, ignore Content items for which it failed
        foreach ($contentItems as $contentId => $content) {
            try {
                $this->fieldHandler->loadExternalFieldData($content);
            } catch (Exception $e) {
                unset($contentItems[$contentId]);
                $this->logger->warning(
                    sprintf(
                        '%s: Content %d not loaded: %s',
                        __METHOD__,
                        $contentId,
                        $e->getMessage()
                    )
                );
            }
        }

        return $contentItems;
    }

    /**
     * Returns the metadata object for a content identified by $contentId.
     *
     * @param int|string $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function loadContentInfo($contentId)
    {
        return $this->treeHandler->loadContentInfo($contentId);
    }

    public function loadContentInfoList(array $contentIds)
    {
        $list = $this->mapper->extractContentInfoFromRows(
            $this->contentGateway->loadContentInfoList($contentIds)
        );

        $listByContentId = [];
        foreach ($list as $item) {
            $listByContentId[$item->id] = $item;
        }

        return $listByContentId;
    }

    /**
     * Returns the metadata object for a content identified by $remoteId.
     *
     * @param mixed $remoteId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        return $this->mapper->extractContentInfoFromRow(
            $this->contentGateway->loadContentInfoByRemoteId($remoteId)
        );
    }

    /**
     * Returns the version object for a content/version identified by $contentId and $versionNo.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If version is not found
     *
     * @param int|string $contentId
     * @param int $versionNo Version number to load
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function loadVersionInfo($contentId, $versionNo)
    {
        $rows = $this->contentGateway->loadVersionInfo($contentId, $versionNo);
        if (empty($rows)) {
            throw new NotFound('content', $contentId);
        }

        $versionInfo = $this->mapper->extractVersionInfoListFromRows(
            $rows,
            $this->contentGateway->loadVersionedNameData([['id' => $contentId, 'version' => $versionNo]])
        );

        return reset($versionInfo);
    }

    /**
     * Returns all versions with draft status created by the given $userId.
     *
     * @param int $userId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo[]
     */
    public function loadDraftsForUser($userId)
    {
        $rows = $this->contentGateway->listVersionsForUser($userId, VersionInfo::STATUS_DRAFT);
        if (empty($rows)) {
            return [];
        }

        $idVersionPairs = array_map(
            function ($row) {
                return [
                    'id' => $row['ezcontentobject_version_contentobject_id'],
                    'version' => $row['ezcontentobject_version_version'],
                ];
            },
            $rows
        );
        $nameRows = $this->contentGateway->loadVersionedNameData($idVersionPairs);

        return $this->mapper->extractVersionInfoListFromRows($rows, $nameRows);
    }

    /**
     * Sets the status of object identified by $contentId and $version to $status.
     *
     * The $status can be one of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
     * When status is set to VersionInfo::STATUS_PUBLISHED content status is updated to ContentInfo::STATUS_PUBLISHED
     *
     * @param int $contentId
     * @param int $status
     * @param int $version
     *
     * @return bool
     */
    public function setStatus($contentId, $status, $version)
    {
        return $this->contentGateway->setStatus($contentId, $version, $status);
    }

    /**
     * Updates a content object meta data, identified by $contentId.
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $content
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function updateMetadata($contentId, MetadataUpdateStruct $content)
    {
        $this->contentGateway->updateContent($contentId, $content);
        $this->updatePathIdentificationString($contentId, $content);

        return $this->loadContentInfo($contentId);
    }

    /**
     * Updates path identification string for locations of given $contentId if main language
     * is set in update struct.
     *
     * This is specific to the Legacy storage engine, as path identification string is deprecated.
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $content
     */
    protected function updatePathIdentificationString($contentId, MetadataUpdateStruct $content)
    {
        if (isset($content->mainLanguageId)) {
            $contentLocationsRows = $this->locationGateway->loadLocationDataByContent($contentId);
            foreach ($contentLocationsRows as $row) {
                $locationName = '';
                $urlAliasRows = $this->urlAliasGateway->loadLocationEntries(
                    $row['node_id'],
                    false,
                    $content->mainLanguageId
                );
                if (!empty($urlAliasRows)) {
                    $locationName = $urlAliasRows[0]['text'];
                }
                $this->locationGateway->updatePathIdentificationString(
                    $row['node_id'],
                    $row['parent_node_id'],
                    $this->slugConverter->convert(
                        $locationName,
                        'node_' . $row['node_id'],
                        'urlalias_compat'
                    )
                );
            }
        }
    }

    /**
     * Updates a content version, identified by $contentId and $versionNo.
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function updateContent($contentId, $versionNo, UpdateStruct $updateStruct)
    {
        $content = $this->load($contentId, $versionNo);
        $this->contentGateway->updateVersion($contentId, $versionNo, $updateStruct);
        $contentType = $this->contentTypeHandler->load($content->versionInfo->contentInfo->contentTypeId);
        $this->fieldHandler->updateFields($content, $updateStruct, $contentType);
        foreach ($updateStruct->name as $language => $name) {
            $this->contentGateway->setName(
                $contentId,
                $versionNo,
                $name,
                $language
            );
        }

        return $this->load($contentId, $versionNo);
    }

    /**
     * Deletes all versions and fields, all locations (subtree), and all relations.
     *
     * Removes the relations, but not the related objects. All subtrees of the
     * assigned nodes of this content objects are removed (recursively).
     *
     * @param int $contentId
     *
     * @return bool
     */
    public function deleteContent($contentId)
    {
        $contentLocations = $this->contentGateway->getAllLocationIds($contentId);
        if (empty($contentLocations)) {
            $this->removeRawContent($contentId);
        } else {
            foreach ($contentLocations as $locationId) {
                $this->treeHandler->removeSubtree($locationId);
            }
        }
    }

    /**
     * Deletes raw content data.
     *
     * @param int $contentId
     */
    public function removeRawContent($contentId)
    {
        $this->treeHandler->removeRawContent($contentId);
    }

    /**
     * Deletes given version, its fields, node assignment, relations and names.
     *
     * Removes the relations, but not the related objects.
     *
     * @param int $contentId
     * @param int $versionNo
     *
     * @return bool
     */
    public function deleteVersion($contentId, $versionNo)
    {
        $versionInfo = $this->loadVersionInfo($contentId, $versionNo);

        $this->locationGateway->deleteNodeAssignment($contentId, $versionNo);

        $this->fieldHandler->deleteFields($contentId, $versionInfo);

        $this->contentGateway->deleteRelations($contentId, $versionNo);
        $this->contentGateway->deleteVersions($contentId, $versionNo);
        $this->contentGateway->deleteNames($contentId, $versionNo);
    }

    /**
     * Returns the versions for $contentId.
     *
     * Result is returned with oldest version first (sorted by created, alternatively version id if auto increment).
     *
     * @param int $contentId
     * @param mixed|null $status Optional argument to filter versions by status, like {@see VersionInfo::STATUS_ARCHIVED}.
     * @param int $limit Limit for items returned, -1 means none.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo[]
     */
    public function listVersions($contentId, $status = null, $limit = -1)
    {
        return $this->treeHandler->listVersions($contentId, $status, $limit);
    }

    /**
     * Copy Content with Fields, Versions & Relations from $contentId in $version.
     *
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content or version is not found
     *
     * @param mixed $contentId
     * @param mixed|null $versionNo Copy all versions if left null
     * @param int|null $newOwnerId
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function copy($contentId, $versionNo = null, $newOwnerId = null)
    {
        $currentVersionNo = isset($versionNo) ?
            $versionNo :
            $this->loadContentInfo($contentId)->currentVersionNo;

        // Copy content in given version or current version
        $createStruct = $this->mapper->createCreateStructFromContent(
            $this->load($contentId, $currentVersionNo)
        );
        if ($newOwnerId) {
            $createStruct->ownerId = $newOwnerId;
        }
        $content = $this->internalCreate($createStruct, $currentVersionNo);

        // If version was not passed also copy other versions
        if (!isset($versionNo)) {
            $contentType = $this->contentTypeHandler->load($createStruct->typeId);

            foreach ($this->listVersions($contentId) as $versionInfo) {
                if ($versionInfo->versionNo === $currentVersionNo) {
                    continue;
                }

                $versionContent = $this->load($contentId, $versionInfo->versionNo);

                $versionContent->versionInfo->contentInfo->id = $content->versionInfo->contentInfo->id;
                $versionContent->versionInfo->modificationDate = $createStruct->modified;
                $versionContent->versionInfo->creationDate = $createStruct->modified;
                $versionContent->versionInfo->id = $this->contentGateway->insertVersion(
                    $versionContent->versionInfo,
                    $versionContent->fields
                );

                $this->fieldHandler->createNewFields($versionContent, $contentType);

                // Create names
                foreach ($versionContent->versionInfo->names as $language => $name) {
                    $this->contentGateway->setName(
                        $content->versionInfo->contentInfo->id,
                        $versionInfo->versionNo,
                        $name,
                        $language
                    );
                }
            }

            // Batch copy relations for all versions
            $this->contentGateway->copyRelations($contentId, $content->versionInfo->contentInfo->id);
        } else {
            // Batch copy relations for published version
            $this->contentGateway->copyRelations($contentId, $content->versionInfo->contentInfo->id, $versionNo);
        }

        return $content;
    }

    /**
     * Creates a relation between $sourceContentId in $sourceContentVersionNo
     * and $destinationContentId with a specific $type.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    public function addRelation(RelationCreateStruct $createStruct)
    {
        $relation = $this->mapper->createRelationFromCreateStruct($createStruct);

        $relation->id = $this->contentGateway->insertRelation($createStruct);

        return $relation;
    }

    /**
     * Removes a relation by relation Id.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param mixed $relationId
     * @param int $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     */
    public function removeRelation($relationId, $type)
    {
        $this->contentGateway->deleteRelation($relationId, $type);
    }

    /**
     * Loads relations from $sourceContentId. Optionally, loads only those with $type and $sourceContentVersionNo.
     *
     * @param mixed $sourceContentId Source Content ID
     * @param mixed|null $sourceContentVersionNo Source Content Version, null if not specified
     * @param int|null $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation[]
     */
    public function loadRelations($sourceContentId, $sourceContentVersionNo = null, $type = null)
    {
        return $this->mapper->extractRelationsFromRows(
            $this->contentGateway->loadRelations($sourceContentId, $sourceContentVersionNo, $type)
        );
    }

    /**
     * Loads relations from $contentId. Optionally, loads only those with $type.
     *
     * Only loads relations against published versions.
     *
     * @param mixed $destinationContentId Destination Content ID
     * @param int|null $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation[]
     */
    public function loadReverseRelations($destinationContentId, $type = null)
    {
        return $this->mapper->extractRelationsFromRows(
            $this->contentGateway->loadReverseRelations($destinationContentId, $type)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslationFromContent($contentId, $languageCode)
    {
        @trigger_error(
            __METHOD__ . ' is deprecated, use deleteTranslationFromContent instead',
            E_USER_DEPRECATED
        );
        $this->deleteTranslationFromContent($contentId, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTranslationFromContent($contentId, $languageCode)
    {
        $this->fieldHandler->deleteTranslationFromContentFields(
            $contentId,
            $this->listVersions($contentId),
            $languageCode
        );
        $this->contentGateway->deleteTranslationFromContent($contentId, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTranslationFromDraft($contentId, $versionNo, $languageCode)
    {
        $versionInfo = $this->loadVersionInfo($contentId, $versionNo);

        $this->fieldHandler->deleteTranslationFromVersionFields(
            $versionInfo,
            $languageCode
        );
        $this->contentGateway->deleteTranslationFromVersion(
            $contentId,
            $versionNo,
            $languageCode
        );

        // get all [languageCode => name] entries except the removed Translation
        $names = array_filter(
            $versionInfo->names,
            function ($lang) use ($languageCode) {
                return $lang !== $languageCode;
            },
            ARRAY_FILTER_USE_KEY
        );
        // set new Content name
        foreach ($names as $language => $name) {
            $this->contentGateway->setName(
                $contentId,
                $versionNo,
                $name,
                $language
            );
        }

        // reload entire Version w/o removed Translation
        return $this->load($contentId, $versionNo);
    }
}
