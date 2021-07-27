<?php

/**
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
        $this->getGroupTags = static function (Type\Group $group) {
            return [TagIdentifiers::TYPE_GROUP . '-' . $group->id];
        };

        $this->getGroupKeys = function (Type\Group $group) {
            return [
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP . '-' . $group->id,
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP . '-' . $this->escapeForCacheKey($group->identifier) . TagIdentifiers::BY_IDENTIFIER_SUFFIX,
            ];
        };

        $this->getTypeTags = static function (Type $type) {
            return [
                TagIdentifiers::TYPE, // For use by deleteByUserAndStatus() as it currently lacks return value for affected type ids
                TagIdentifiers::TYPE . '-' . $type->id,
            ];
        };
        $this->getTypeKeys = function (Type $type, int $status = Type::STATUS_DEFINED) {
            return [
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-' . $type->id,
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-' . $type->id . '-' . $status,
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-' . $this->escapeForCacheKey($type->identifier) . TagIdentifiers::BY_IDENTIFIER_SUFFIX,
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-' . $this->escapeForCacheKey($type->remoteId) . TagIdentifiers::BY_REMOTE_SUFFIX,
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function createGroup(GroupCreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $this->cache->deleteItems([TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP_LIST]);

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
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP_LIST,
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP . '-' . $struct->id,
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP . '-' . $this->escapeForCacheKey($struct->identifier) . TagIdentifiers::BY_IDENTIFIER_SUFFIX,
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

        $this->cache->invalidateTags([TagIdentifiers::TYPE_GROUP . '-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        return $this->getCacheValue(
            $groupId,
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP . '-',
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
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP . '-',
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
            $this->escapeForCacheKey($identifier),
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP . '-',
            function () use ($identifier) {
                return $this->persistenceHandler->contentTypeHandler()->loadGroupByIdentifier($identifier);
            },
            $this->getGroupTags,
            $this->getGroupKeys,
            TagIdentifiers::BY_IDENTIFIER_SUFFIX
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups()
    {
        return $this->getListCacheValue(
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_GROUP_LIST,
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
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_LIST_BY_GROUP . '-' . $groupId,
            function () use ($groupId, $status) {
                return $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            // Add tag in case of empty list
            static function () use ($groupId) { return [TagIdentifiers::TYPE_GROUP . '-' . $groupId]; },
            [$groupId]
        );
    }

    public function loadContentTypeList(array $contentTypeIds): array
    {
        return $this->getMultipleCacheValues(
            $contentTypeIds,
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-',
            function (array $contentTypeIds) {
                return $this->persistenceHandler->contentTypeHandler()->loadContentTypeList($contentTypeIds);
            },
            $this->getTypeTags,
            $this->getTypeKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function load($typeId, $status = Type::STATUS_DEFINED)
    {
        if ($status !== Type::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, ['type' => $typeId, 'status' => $status]);

            return $this->persistenceHandler->contentTypeHandler()->load($typeId, $status);
        }

        $getTypeKeysFn = $this->getTypeKeys;

        return $this->getCacheValue(
            $typeId,
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-',
            function ($typeId) use ($status) {
                return $this->persistenceHandler->contentTypeHandler()->load($typeId, $status);
            },
            $this->getTypeTags,
            static function (Type $type) use ($status, $getTypeKeysFn) {
                return $getTypeKeysFn($type, $status);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier)
    {
        return $this->getCacheValue(
            $this->escapeForCacheKey($identifier),
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-',
            function () use ($identifier) {
                return $this->persistenceHandler->contentTypeHandler()->loadByIdentifier($identifier);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            TagIdentifiers::BY_IDENTIFIER_SUFFIX
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId)
    {
        return $this->getCacheValue(
            $this->escapeForCacheKey($remoteId),
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE . '-',
            function () use ($remoteId) {
                return $this->persistenceHandler->contentTypeHandler()->loadByRemoteId($remoteId);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            TagIdentifiers::BY_REMOTE_SUFFIX
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
                return TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_LIST_BY_GROUP . '-' . $groupId;
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
            $this->cache->invalidateTags([
                TagIdentifiers::TYPE . '-' . $typeId,
                TagIdentifiers::TYPE_MAP,
                TagIdentifiers::CONTENT_FIELDS_TYPE . '-' . $typeId,
            ]);
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
            $this->cache->invalidateTags([
                TagIdentifiers::TYPE . '-' . $typeId,
                TagIdentifiers::TYPE_MAP,
                TagIdentifiers::CONTENT_FIELDS_TYPE . '-' . $typeId,
            ]);
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

        return $draft;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($userId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId, 'type' => $typeId, 'status' => $status]);
        $copy = $this->persistenceHandler->contentTypeHandler()->copy($userId, $typeId, $status);

        // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
        $this->cache->deleteItems(array_map(
            static function ($groupId) {
                return TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_LIST_BY_GROUP . '-' . $groupId;
            },
            $copy->groupIds
        ));

        return $copy;
    }

    /**
     * {@inheritdoc}
     */
    public function unlink($groupId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, ['group' => $groupId, 'type' => $typeId, 'status' => $status]);
        $return = $this->persistenceHandler->contentTypeHandler()->unlink($groupId, $typeId, $status);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags([TagIdentifiers::TYPE . '-' . $typeId]);
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
            $this->cache->invalidateTags([TagIdentifiers::TYPE . '-' . $typeId]);
            // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
            $this->cache->deleteItems([TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_LIST_BY_GROUP . '-' . $groupId]);
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
            $this->cache->invalidateTags([
                TagIdentifiers::TYPE . '-' . $typeId,
                TagIdentifiers::TYPE_MAP,
                TagIdentifiers::CONTENT_FIELDS_TYPE . '-' . $typeId,
            ]);
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
            $this->cache->invalidateTags([
                TagIdentifiers::TYPE . '-' . $typeId,
                TagIdentifiers::TYPE_MAP,
                TagIdentifiers::CONTENT_FIELDS_TYPE . '-' . $typeId,
            ]);
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
            $this->cache->invalidateTags([
                TagIdentifiers::TYPE . '-' . $typeId,
                TagIdentifiers::TYPE_MAP,
                TagIdentifiers::CONTENT_FIELDS_TYPE . '-' . $typeId,
            ]);
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
        $this->cache->invalidateTags([
            TagIdentifiers::TYPE . '-' . $typeId,
            TagIdentifiers::TYPE_MAP,
            TagIdentifiers::CONTENT_FIELDS_TYPE . '-' . $typeId,
        ]);

        // Clear Content Type Groups list cache
        $contentType = $this->load($typeId);
        $this->cache->deleteItems(
            array_map(
                function ($groupId) {
                    return TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_LIST_BY_GROUP . '-' . $groupId;
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
            TagIdentifiers::PREFIX . TagIdentifiers::CONTENT_TYPE_FIELD_MAP,
            function () {
                return $this->persistenceHandler->contentTypeHandler()->getSearchableFieldMap();
            },
            static function () {return [];},
            static function () {return [];},
            static function () { return [TagIdentifiers::TYPE_MAP]; }
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

        $this->cache->invalidateTags([
            TagIdentifiers::TYPE . '-' . $contentTypeId,
            TagIdentifiers::TYPE_MAP,
            TagIdentifiers::CONTENT_FIELDS_TYPE . '-' . $contentTypeId,
        ]);

        return $return;
    }

    public function deleteByUserAndStatus(int $userId, int $status): void
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId, 'status' => $status]);

        $this->persistenceHandler->contentTypeHandler()->deleteByUserAndStatus($userId, $status);
        if ($status === Type::STATUS_DEFINED) {
            // As we don't have indication of affected type id's yet here, we need to clear all type cache for now.
            $this->cache->invalidateTags([TagIdentifiers::TYPE]);
        }
    }
}
