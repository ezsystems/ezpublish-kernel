<?php

/**
 * File containing the ContentTypeHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;

/**
 * ContentType cache.
 *
 * Caches defined (published) content types and content type groups.
 *
 * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler
 */
class ContentTypeHandler extends AbstractInMemoryPersistenceHandler implements ContentTypeHandlerInterface
{
    /** @var callable */
    private $getGroupTags;

    /** @var callable */
    private $getGroupKeys;

    /** @var callable */
    private $getTypeTags;

    /** @var callable */
    private $getTypeKeys;

    /**
     * Set callback functions for use in cache retrival.
     */
    protected function init(): void
    {
        $this->getGroupTags = static function (Type\Group $group) { return ['type-group-' . $group->id]; };
        $this->getGroupKeys = static function (Type\Group $group) {
            return [
                'ez-content-type-group-' . $group->id,
                'ez-content-type-group-' . $group->identifier . '-by-identifier',
            ];
        };

        $this->getTypeTags = static function (Type $type) {
            return [
                'type',
                'type-' . $type->id,
                'type-modifierId-' . $type->modifierId,
                'type-creatorId-' . $type->creatorId,
            ];
        };
        $this->getTypeKeys = static function (Type $type, int $status = Type::STATUS_DEFINED) {
            return [
                'ez-content-type-' . $type->id . '-' . $status,
                'ez-content-type-' . $type->identifier . '-by-identifier',
                'ez-content-type-' . $type->remoteId . '-by-remote',
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function createGroup(GroupCreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $this->cache->deleteItems(['ez-content-type-group-list']);

        return $this->persistenceHandler->contentTypeHandler()->createGroup($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup(GroupUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $group = $this->persistenceHandler->contentTypeHandler()->updateGroup($struct);

        $this->cache->deleteItems([
            'ez-content-type-group-list',
            'ez-content-type-group-' . $struct->id,
            'ez-content-type-group-' . $struct->identifier . '-by-identifier',
        ]);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, ['group' => $groupId]);
        $return = $this->persistenceHandler->contentTypeHandler()->deleteGroup($groupId);

        $this->cache->invalidateTags(['type-group-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        return $this->getCacheValue(
            $groupId,
            'ez-content-type-group-',
            function ($groupId) {
                return $this->persistenceHandler->contentTypeHandler()->loadGroup($groupId);
            },
            $this->getGroupTags,
            $this->getGroupKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroups(array $groupIds)
    {
        return $this->getMultipleCacheValues(
            $groupIds,
            'ez-content-type-group-',
            function (array $groupIds) {
                return $this->persistenceHandler->contentTypeHandler()->loadGroups($groupIds);
            },
            $this->getGroupTags,
            $this->getGroupKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroupByIdentifier($identifier)
    {
        return $this->getCacheValue(
            $identifier,
            'ez-content-type-group-',
            function (string $identifier) {
                return $this->persistenceHandler->contentTypeHandler()->loadGroupByIdentifier($identifier);
            },
            $this->getGroupTags,
            $this->getGroupKeys,
            '-by-identifier'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups()
    {
        return $this->getListCacheValue(
            'ez-content-type-group-list',
            function () {
                return $this->persistenceHandler->contentTypeHandler()->loadAllGroups();
            },
            $this->getGroupTags,
            $this->getGroupKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypes($groupId, $status = Type::STATUS_DEFINED)
    {
        if ($status !== Type::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, ['group' => $groupId, 'status' => $status]);

            return $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);
        }

        return $this->getListCacheValue(
            'ez-content-type-list-by-group-' . $groupId,
            function () use ($groupId, $status) {
                return $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            // Add tag in case of empty list
            static function () use ($groupId) { return ['type-group-' . $groupId]; },
            [$groupId]
        );
    }

    public function loadContentTypeList(array $contentTypeIds): array
    {
        return $this->getMultipleCacheValues(
            $contentTypeIds,
            'ez-content-type-',
            function (array $contentTypeIds) {
                return $this->persistenceHandler->contentTypeHandler()->loadContentTypeList($contentTypeIds);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            '-' . Type::STATUS_DEFINED
        );
    }

    /**
     * {@inheritdoc}
     */
    public function load($typeId, $status = Type::STATUS_DEFINED)
    {
        $getTypeKeysFn = $this->getTypeKeys;

        return $this->getCacheValue(
            $typeId,
            'ez-content-type-',
            function ($typeId) use ($status) {
                return $this->persistenceHandler->contentTypeHandler()->load($typeId, $status);
            },
            $this->getTypeTags,
            static function (Type $type) use ($status, $getTypeKeysFn) {
                return $getTypeKeysFn($type, $status);
            },
            '-' . $status
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier)
    {
        return $this->getCacheValue(
            $identifier,
            'ez-content-type-',
            function ($identifier) {
                return $this->persistenceHandler->contentTypeHandler()->loadByIdentifier($identifier);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            '-by-identifier'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId)
    {
        return $this->getCacheValue(
            $remoteId,
            'ez-content-type-',
            function ($remoteId) {
                return $this->persistenceHandler->contentTypeHandler()->loadByRemoteId($remoteId);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            '-by-remote'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);

        $type = $this->persistenceHandler->contentTypeHandler()->create($struct);

        // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
        $this->cache->deleteItems(array_map(
            function ($groupId) {
                return 'ez-content-type-list-by-group-' . $groupId;
            },
            $struct->groupIds
        ));

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function update($typeId, $status, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['type' => $typeId, 'status' => $status, 'struct' => $struct]);
        $type = $this->persistenceHandler->contentTypeHandler()->update($typeId, $status, $struct);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . $status]);
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($typeId, $status)
    {
        $this->logger->logCall(__METHOD__, ['type' => $typeId, 'status' => $status]);
        $return = $this->persistenceHandler->contentTypeHandler()->delete($typeId, $status);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . $status]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function createDraft($modifierId, $typeId)
    {
        $this->logger->logCall(__METHOD__, ['modifier' => $modifierId, 'type' => $typeId]);
        $draft = $this->persistenceHandler->contentTypeHandler()->createDraft($modifierId, $typeId);

        $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . Type::STATUS_DRAFT]);

        return $draft;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($userId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId, 'type' => $typeId, 'status' => $status]);

        return $this->persistenceHandler->contentTypeHandler()->copy($userId, $typeId, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function unlink($groupId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, ['group' => $groupId, 'type' => $typeId, 'status' => $status]);
        $return = $this->persistenceHandler->contentTypeHandler()->unlink($groupId, $typeId, $status);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId]);
        } else {
            $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . $status]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function link($groupId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, ['group' => $groupId, 'type' => $typeId, 'status' => $status]);
        $return = $this->persistenceHandler->contentTypeHandler()->link($groupId, $typeId, $status);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId]);
            // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
            $this->cache->deleteItems(['ez-content-type-list-by-group-' . $groupId]);
        } else {
            $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . $status]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($id, $status)
    {
        $this->logger->logCall(__METHOD__, ['field' => $id, 'status' => $status]);

        return $this->persistenceHandler->contentTypeHandler()->getFieldDefinition($id, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentCount($contentTypeId)
    {
        $this->logger->logCall(__METHOD__, ['contentTypeId' => $contentTypeId]);

        return $this->persistenceHandler->contentTypeHandler()->getContentCount($contentTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldDefinition($typeId, $status, FieldDefinition $struct)
    {
        $this->logger->logCall(__METHOD__, ['type' => $typeId, 'status' => $status, 'struct' => $struct]);
        $return = $this->persistenceHandler->contentTypeHandler()->addFieldDefinition(
            $typeId,
            $status,
            $struct
        );

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . $status]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFieldDefinition($typeId, $status, $fieldDefinitionId)
    {
        $this->logger->logCall(__METHOD__, ['type' => $typeId, 'status' => $status, 'field' => $fieldDefinitionId]);
        $this->persistenceHandler->contentTypeHandler()->removeFieldDefinition(
            $typeId,
            $status,
            $fieldDefinitionId
        );

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . $status]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateFieldDefinition($typeId, $status, FieldDefinition $struct)
    {
        $this->logger->logCall(__METHOD__, ['type' => $typeId, 'status' => $status, 'struct' => $struct]);
        $this->persistenceHandler->contentTypeHandler()->updateFieldDefinition(
            $typeId,
            $status,
            $struct
        );

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItems(['ez-content-type-' . $typeId . '-' . $status]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish($typeId)
    {
        $this->logger->logCall(__METHOD__, ['type' => $typeId]);
        $this->persistenceHandler->contentTypeHandler()->publish($typeId);

        // Clear type cache, map cache, and content cache which contains fields.
        $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);

        // Clear Content Type Groups list cache
        $contentType = $this->load($typeId);
        $this->cache->deleteItems(
            array_map(
                function ($groupId) {
                    return 'ez-content-type-list-by-group-' . $groupId;
                },
                $contentType->groupIds
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableFieldMap()
    {
        return $this->getListCacheValue(
            'ez-content-type-field-map',
            function () {
                return $this->persistenceHandler->contentTypeHandler()->getSearchableFieldMap();
            },
            static function () {return [];},
            static function () {return [];},
            static function () { return ['type-map']; }
        );
    }

    /**
     * @param int $contentTypeId
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function removeContentTypeTranslation(int $contentTypeId, string $languageCode): Type
    {
        $this->logger->logCall(__METHOD__, ['id' => $contentTypeId, 'languageCode' => $languageCode]);
        $return = $this->persistenceHandler->contentTypeHandler()->removeContentTypeTranslation(
            $contentTypeId,
            $languageCode
        );

        $this->cache->invalidateTags(['type-' . $contentTypeId, 'type-map', 'content-fields-type-' . $contentTypeId]);

        return $return;
    }

    public function deleteByUserAndStatus(int $userId, int $status): void
    {
        $this->persistenceHandler->contentTypeHandler()->deleteByUserAndStatus($userId, $status);
        $this->cache->invalidateTags(['type-modifierId-' . $userId, 'type-creatorId-' . $userId]);
    }
}
