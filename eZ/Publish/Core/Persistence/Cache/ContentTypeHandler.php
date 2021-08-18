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
    private const TYPE_TAG = 'type';
    private const TYPE_GROUP_TAG = 'type_group';
    private const CONTENT_TYPE_TAG = 'content_type';
    private const CONTENT_TYPE_GROUP_TAG = 'content_type_group';
    private const CONTENT_TYPE_GROUP_WITH_ID_SUFFIX_TAG = 'content_type_group_with_id_suffix';
    private const CONTENT_TYPE_GROUP_WITH_BY_REMOTE_SUFFIX_TAG = 'content_type_group_with_by_remote_suffix';
    private const CONTENT_TYPE_GROUP_LIST_TAG = 'content_type_group_list';
    private const BY_IDENTIFIER_SUFFIX = 'by_identifier_suffix';
    private const CONTENT_TYPE_LIST_BY_GROUP_TAG = 'content_type_list_by_group';
    private const BY_REMOTE_SUFFIX = 'by_remote_suffix';
    private const TYPE_MAP_TAG = 'type_map';
    private const CONTENT_FIELDS_TYPE_TAG = 'content_fields_type';
    private const TYPE_WITHOUT_VALUE_TAG = 'type_without_value';
    private const CONTENT_TYPE_FIELD_MAP_TAG = 'content_type_field_map';

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
        $tagGenerator = $this->tagGenerator;

        $this->getGroupTags = static function (Type\Group $group) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::TYPE_GROUP_TAG, [$group->id]),
            ];
        };

        $this->getGroupKeys = function (Type\Group $group) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::CONTENT_TYPE_GROUP_TAG, [$group->id]),
                $tagGenerator->generate(
                    self::CONTENT_TYPE_GROUP_WITH_ID_SUFFIX_TAG,
                    [$this->escapeForCacheKey($group->identifier)],
                    true
                ),
            ];
        };

        $this->getTypeTags = static function (Type $type) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::TYPE_TAG), // For use by deleteByUserAndStatus() as it currently lacks return value for affected type ids
                $tagGenerator->generate(self::TYPE_TAG, [$type->id]),
            ];
        };
        $this->getTypeKeys = function (Type $type, int $status = Type::STATUS_DEFINED) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::CONTENT_TYPE_TAG, [$type->id], true),
                $tagGenerator->generate(self::CONTENT_TYPE_TAG, [$type->id], true) . '-' . $status,
                $tagGenerator->generate(
                    self::CONTENT_TYPE_GROUP_WITH_ID_SUFFIX_TAG,
                    [$this->escapeForCacheKey($type->identifier)],
                    true
                ),
                $tagGenerator->generate(
                    self::CONTENT_TYPE_GROUP_WITH_BY_REMOTE_SUFFIX_TAG,
                    [$this->escapeForCacheKey($type->remoteId)],
                    true
                ),
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function createGroup(GroupCreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $this->cache->deleteItems([
            $this->tagGenerator->generate(self::CONTENT_TYPE_GROUP_LIST_TAG, [], true),
        ]);

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
            $this->tagGenerator->generate(self::CONTENT_TYPE_GROUP_LIST_TAG, [], true),
            $this->tagGenerator->generate(self::CONTENT_TYPE_GROUP_TAG, [$struct->id], true),
            $this->tagGenerator->generate(
                self::CONTENT_TYPE_GROUP_WITH_ID_SUFFIX_TAG,
                [$this->escapeForCacheKey($struct->identifier)],
                true
            ),
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

        $this->cache->invalidateTags([
            $this->tagGenerator->generate(self::TYPE_GROUP_TAG, [$groupId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        return $this->getCacheValue(
            $groupId,
            $this->tagGenerator->generate(self::CONTENT_TYPE_GROUP_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::CONTENT_TYPE_GROUP_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::CONTENT_TYPE_GROUP_TAG, [], true) . '-',
            function () use ($identifier) {
                return $this->persistenceHandler->contentTypeHandler()->loadGroupByIdentifier($identifier);
            },
            $this->getGroupTags,
            $this->getGroupKeys,
            $this->tagGenerator->generate(self::BY_IDENTIFIER_SUFFIX)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups()
    {
        return $this->getListCacheValue(
            $this->tagGenerator->generate(self::CONTENT_TYPE_GROUP_LIST_TAG, [], true),
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
        $tagGenerator = $this->tagGenerator;

        if ($status !== Type::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, ['group' => $groupId, 'status' => $status]);

            return $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);
        }

        return $this->getListCacheValue(
            $tagGenerator->generate(self::CONTENT_TYPE_LIST_BY_GROUP_TAG, [$groupId], true),
            function () use ($groupId, $status) {
                return $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            // Add tag in case of empty list
            static function () use ($groupId, $tagGenerator) {
                return [
                    $tagGenerator->generate(self::TYPE_GROUP_TAG, [$groupId]),
                ];
            },
            [$groupId]
        );
    }

    public function loadContentTypeList(array $contentTypeIds): array
    {
        return $this->getMultipleCacheValues(
            $contentTypeIds,
            $this->tagGenerator->generate(self::CONTENT_TYPE_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::CONTENT_TYPE_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::CONTENT_TYPE_TAG, [], true) . '-',
            function () use ($identifier) {
                return $this->persistenceHandler->contentTypeHandler()->loadByIdentifier($identifier);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            $this->tagGenerator->generate(self::BY_IDENTIFIER_SUFFIX)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId)
    {
        return $this->getCacheValue(
            $this->escapeForCacheKey($remoteId),
            $this->tagGenerator->generate(self::CONTENT_TYPE_TAG, [], true) . '-',
            function () use ($remoteId) {
                return $this->persistenceHandler->contentTypeHandler()->loadByRemoteId($remoteId);
            },
            $this->getTypeTags,
            $this->getTypeKeys,
            $this->tagGenerator->generate(self::BY_REMOTE_SUFFIX)
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
                return $this->tagGenerator->generate(
                    self::CONTENT_TYPE_LIST_BY_GROUP_TAG,
                    [$groupId],
                    true
                );
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
                $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
                $this->tagGenerator->generate(self::TYPE_MAP_TAG),
                $this->tagGenerator->generate(self::CONTENT_FIELDS_TYPE_TAG, [$typeId]),
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
                $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
                $this->tagGenerator->generate(self::TYPE_MAP_TAG),
                $this->tagGenerator->generate(self::CONTENT_FIELDS_TYPE_TAG, [$typeId]),
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

        return $this->persistenceHandler->contentTypeHandler()->createDraft($modifierId, $typeId);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($userId, $typeId, $status)
    {
        $tagGenerator = $this->tagGenerator;
        $this->logger->logCall(__METHOD__, ['user' => $userId, 'type' => $typeId, 'status' => $status]);
        $copy = $this->persistenceHandler->contentTypeHandler()->copy($userId, $typeId, $status);

        // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
        $this->cache->deleteItems(array_map(
            static function ($groupId) use ($tagGenerator) {
                return $tagGenerator->generate(self::CONTENT_TYPE_LIST_BY_GROUP_TAG, [$groupId], true);
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
            $this->cache->invalidateTags([
                $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
            ]);
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
            $this->cache->invalidateTags([
                $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
            ]);
            // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
            $this->cache->deleteItems([
                $this->tagGenerator->generate(
                    self::CONTENT_TYPE_LIST_BY_GROUP_TAG,
                    [$groupId],
                    true
                ),
            ]);
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
                $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
                $this->tagGenerator->generate(self::TYPE_MAP_TAG),
                $this->tagGenerator->generate(self::CONTENT_FIELDS_TYPE_TAG, [$typeId]),
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
                $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
                $this->tagGenerator->generate(self::TYPE_MAP_TAG),
                $this->tagGenerator->generate(self::CONTENT_FIELDS_TYPE_TAG, [$typeId]),
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
                $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
                $this->tagGenerator->generate(self::TYPE_MAP_TAG),
                $this->tagGenerator->generate(self::CONTENT_FIELDS_TYPE_TAG, [$typeId]),
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
            $this->tagGenerator->generate(self::TYPE_TAG, [$typeId]),
            $this->tagGenerator->generate(self::TYPE_MAP_TAG),
            $this->tagGenerator->generate(self::CONTENT_FIELDS_TYPE_TAG, [$typeId]),
        ]);

        // Clear Content Type Groups list cache
        $contentType = $this->load($typeId);
        $this->cache->deleteItems(
            array_map(
                function ($groupId) {
                    return $this->tagGenerator->generate(
                        self::CONTENT_TYPE_LIST_BY_GROUP_TAG,
                        [$groupId],
                        true
                    );
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
        $tagGenerator = $this->tagGenerator;

        return $this->getListCacheValue(
            $tagGenerator->generate(self::CONTENT_TYPE_FIELD_MAP_TAG, [], true),
            function () {
                return $this->persistenceHandler->contentTypeHandler()->getSearchableFieldMap();
            },
            static function () {return [];},
            static function () {return [];},
            static function () use ($tagGenerator) {
                return [
                    $tagGenerator->generate(self::TYPE_MAP_TAG),
                ];
            }
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
            $this->tagGenerator->generate(self::TYPE_TAG, [$contentTypeId]),
            $this->tagGenerator->generate(self::TYPE_MAP_TAG),
            $this->tagGenerator->generate(self::CONTENT_FIELDS_TYPE_TAG, [$contentTypeId]),
        ]);

        return $return;
    }

    public function deleteByUserAndStatus(int $userId, int $status): void
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId, 'status' => $status]);

        $this->persistenceHandler->contentTypeHandler()->deleteByUserAndStatus($userId, $status);
        if ($status === Type::STATUS_DEFINED) {
            // As we don't have indication of affected type id's yet here, we need to clear all type cache for now.
            $this->cache->invalidateTags([
                $this->tagGenerator->generate(self::TYPE_WITHOUT_VALUE_TAG),
            ]);
        }
    }
}
