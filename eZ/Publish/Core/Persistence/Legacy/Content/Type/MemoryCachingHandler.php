<?php

/**
 * File containing the In Memory Caching Content Type Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as BaseContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Exception;

class MemoryCachingHandler implements BaseContentTypeHandler
{
    /**
     * Inner handler to dispatch calls to.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $innerHandler;

    /**
     * Local in-memory cache for groups in one single request.
     *
     * @var array
     */
    protected $groups = [];

    /**
     * Local in-memory cache for content types in one single request.
     *
     * @var array
     */
    protected $contentTypes = [];

    /**
     * Local in-memory cache for field definitions in one single request.
     *
     * @var array
     */
    protected $fieldDefinitions;

    /**
     * Local in-memory cache for searchable field map in one single request.
     *
     * @var array
     */
    protected $searchableFieldMap = null;

    /**
     * Creates a new content type handler.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $handler
     */
    public function __construct(BaseContentTypeHandler $handler)
    {
        $this->innerHandler = $handler;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct $createStruct
     *
     * @return Group
     */
    public function createGroup(GroupCreateStruct $createStruct)
    {
        $this->clearCache();

        return $this->innerHandler->createGroup($createStruct);
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group
     */
    public function updateGroup(GroupUpdateStruct $struct)
    {
        $this->clearCache();

        return $this->innerHandler->updateGroup($struct);
    }

    /**
     * @param mixed $groupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If type group contains types
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type group with id is not found
     */
    public function deleteGroup($groupId)
    {
        $this->clearCache();

        return $this->innerHandler->deleteGroup($groupId);
    }

    /**
     * @param mixed $groupId
     *
     * @return Group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type group with $groupId is not found
     */
    public function loadGroup($groupId)
    {
        if (isset($this->groups[$groupId])) {
            return $this->groups[$groupId];
        }

        return $this->groups[$groupId] = $this->innerHandler->loadGroup($groupId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroups(array $groupIds)
    {
        $groups = $missingIds = [];
        foreach ($groupIds as $groupId) {
            if (isset($this->groups[$groupId])) {
                $groups[$groupId] = $this->groups[$groupId];
            } else {
                $missingIds[] = $groupId;
            }
        }

        if (!empty($missingIds)) {
            $missing = $this->innerHandler->loadGroups($missingIds);
            $groups += $missing;
            $this->groups += $missing;
        }

        return $groups;
    }

    /**
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type group with $identifier is not found
     */
    public function loadGroupByIdentifier($identifier)
    {
        if (isset($this->groups[$identifier])) {
            return $this->groups[$identifier];
        }

        return $this->groups[$identifier] = $this->innerHandler->loadGroupByIdentifier($identifier);
    }

    /**
     * @return Group[]
     */
    public function loadAllGroups()
    {
        return $this->innerHandler->loadAllGroups();
    }

    /**
     * @param mixed $groupId
     * @param int $status
     *
     * @return Type[]
     */
    public function loadContentTypes($groupId, $status = 0)
    {
        return $this->innerHandler->loadContentTypes($groupId, $status);
    }

    public function loadContentTypeList(array $contentTypeIds): array
    {
        $contentTypes = $missingIds = [];
        foreach ($contentTypeIds as $contentTypeId) {
            if (isset($this->contentTypes['id'][$contentTypeId])) {
                $contentTypes[$contentTypeId] = $this->contentTypes['id'][$contentTypeId];
            } else {
                $missingIds[] = $contentTypeId;
            }
        }

        if (!empty($missingIds)) {
            $missing = $this->innerHandler->loadContentTypeList($missingIds);
            $contentTypes += $missing;
            if (empty($this->contentTypes['id'])) {
                $this->contentTypes['id'] = $missing;
            } else {
                $this->contentTypes['id'] += $missing;
            }
        }

        return $contentTypes;
    }

    /**
     * @param int $contentTypeId
     * @param int $status
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function load($contentTypeId, $status = Type::STATUS_DEFINED)
    {
        if (isset($this->contentTypes['id'][$contentTypeId][$status])) {
            return $this->contentTypes['id'][$contentTypeId][$status];
        }

        return $this->contentTypes['id'][$contentTypeId][$status] =
            $this->innerHandler->load($contentTypeId, $status);
    }

    /**
     * Load a (defined) content type by identifier.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If defined type is not found
     */
    public function loadByIdentifier($identifier)
    {
        if (isset($this->contentTypes['identifier'][$identifier])) {
            return $this->contentTypes['identifier'][$identifier];
        }

        return $this->contentTypes['identifier'][$identifier] =
            $this->innerHandler->loadByIdentifier($identifier);
    }

    /**
     * Load a (defined) content type by remote id.
     *
     * @param mixed $remoteId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If defined type is not found
     */
    public function loadByRemoteId($remoteId)
    {
        if (isset($this->contentTypes['remoteId'][$remoteId])) {
            return $this->contentTypes['remoteId'][$remoteId];
        }

        return $this->contentTypes['remoteId'][$remoteId] =
            $this->innerHandler->loadByRemoteId($remoteId);
    }

    /**
     * Loads a single Type from $rows.
     *
     * @param array $rows
     * @param mixed $typeIdentifier
     * @param int $status
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function loadFromRows(array $rows, $typeIdentifier, $status)
    {
        $types = $this->mapper->extractTypesFromRows($rows);
        if (!isset($types[0])) {
            throw new Exception\TypeNotFound($typeIdentifier, $status);
        }

        return $types[0];
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function create(CreateStruct $createStruct)
    {
        $this->clearCache();

        return $this->innerHandler->create($createStruct);
    }

    /**
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $contentType
     *
     * @return Type
     */
    public function update($typeId, $status, UpdateStruct $contentType)
    {
        $this->clearCache();

        return $this->innerHandler->update($typeId, $status, $contentType);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If type is defined and still has content
     *
     * @param mixed $contentTypeId
     * @param int $status
     *
     * @return bool
     */
    public function delete($contentTypeId, $status)
    {
        $this->clearCache();

        return $this->innerHandler->delete($contentTypeId, $status);
    }

    /**
     * Creates a draft of existing defined content type.
     *
     * Updates modified date, sets $modifierId and status to Type::STATUS_DRAFT on the new returned draft.
     *
     * @param mixed $modifierId
     * @param mixed $contentTypeId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type with defined status is not found
     */
    public function createDraft($modifierId, $contentTypeId)
    {
        $this->clearCache();

        return $this->innerHandler->createDraft($modifierId, $contentTypeId);
    }

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @return Type
     */
    public function copy($userId, $contentTypeId, $status)
    {
        $this->clearCache();

        return $this->innerHandler->copy($userId, $contentTypeId, $status);
    }

    /**
     * Unlink a content type group from a content type.
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group or type with provided status is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If $groupId is last group on $contentTypeId or
     *                                                                 not a group assigned to type
     *
     * @todo Add throws for NotFound and BadState when group is not assigned to type
     */
    public function unlink($groupId, $contentTypeId, $status)
    {
        $this->clearCache();

        return $this->innerHandler->unlink($groupId, $contentTypeId, $status);
    }

    /**
     * Link a content type group with a content type.
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group or type with provided status is not found
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If type is already part of group
     *
     * @todo Above throws are not implemented
     */
    public function link($groupId, $contentTypeId, $status)
    {
        $this->clearCache();

        return $this->innerHandler->link($groupId, $contentTypeId, $status);
    }

    /**
     * Returns field definition for the given field definition id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If field definition is not found
     *
     * @param mixed $id
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function getFieldDefinition($id, $status)
    {
        if (isset($this->fieldDefinitions[$id][$status])) {
            return $this->fieldDefinitions[$id][$status];
        }

        return $this->fieldDefinitions[$id][$status] =
            $this->innerHandler->getFieldDefinition($id, $status);
    }

    /**
     * Counts the number of Content instances of the ContentType identified by given $contentTypeId.
     *
     * @param mixed $contentTypeId
     *
     * @return int
     */
    public function getContentCount($contentTypeId)
    {
        return $this->innerHandler->getContentCount($contentTypeId);
    }

    /**
     * Adds a new field definition to an existing Type.
     *
     * This method creates a new status of the Type with the $fieldDefinition
     * added. It does not update existing content objects depending on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     */
    public function addFieldDefinition($contentTypeId, $status, FieldDefinition $fieldDefinition)
    {
        $this->clearCache();

        return $this->innerHandler->addFieldDefinition($contentTypeId, $status, $fieldDefinition);
    }

    /**
     * Removes a field definition from an existing Type.
     *
     * This method creates a new status of the Type with the field definition
     * referred to by $fieldDefinitionId removed. It does not update existing
     * content objects depending on the field (default) values.
     *
     * @param mixed $contentTypeId
     * @param mixed $fieldDefinitionId
     *
     * @return bool
     */
    public function removeFieldDefinition($contentTypeId, $status, $fieldDefinitionId)
    {
        $this->clearCache();

        return $this->innerHandler->removeFieldDefinition($contentTypeId, $status, $fieldDefinitionId);
    }

    /**
     * This method updates the given $fieldDefinition on a Type.
     *
     * This method creates a new status of the Type with the updated
     * $fieldDefinition. It does not update existing content objects depending
     * on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     */
    public function updateFieldDefinition($contentTypeId, $status, FieldDefinition $fieldDefinition)
    {
        $this->clearCache();

        return $this->innerHandler->updateFieldDefinition($contentTypeId, $status, $fieldDefinition);
    }

    /**
     * Update content objects.
     *
     * Updates content objects, depending on the changed field definitions.
     *
     * A content type has a state which tells if its content objects yet have
     * been adapted.
     *
     * Flags the content type as updated.
     *
     * @param mixed $contentTypeId
     */
    public function publish($contentTypeId)
    {
        $this->clearCache();

        return $this->innerHandler->publish($contentTypeId);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::getSearchableFieldMap
     */
    public function getSearchableFieldMap()
    {
        if ($this->searchableFieldMap !== null) {
            return $this->searchableFieldMap;
        }

        return $this->searchableFieldMap = $this->innerHandler->getSearchableFieldMap();
    }

    /**
     * Clear internal caches.
     */
    public function clearCache()
    {
        $this->groups = $this->contentTypes = $this->fieldDefinitions = array();
        $this->searchableFieldMap = null;
    }
}
