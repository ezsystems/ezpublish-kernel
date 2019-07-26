<?php

/**
 * File containing the In Memory Caching Content Type Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type;

use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as BaseContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;

class MemoryCachingHandler implements BaseContentTypeHandler
{
    /**
     * Inner handler to dispatch calls to.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $innerHandler;

    /**
     * Type cache.
     *
     * @var \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache
     */
    protected $cache;

    /**
     * Creates a new content type handler.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $handler
     * @param \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache $cache
     */
    public function __construct(BaseContentTypeHandler $handler, InMemoryCache $cache)
    {
        $this->innerHandler = $handler;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function createGroup(GroupCreateStruct $createStruct)
    {
        $group = $this->innerHandler->createGroup($createStruct);
        $this->storeGroupCache([$group]);
        $this->cache->deleteMulti(['ez-content-type-group-list']);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup(GroupUpdateStruct $struct)
    {
        $group = $this->innerHandler->updateGroup($struct);
        $this->storeGroupCache([$group]);
        $this->cache->deleteMulti(['ez-content-type-group-list']);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->innerHandler->deleteGroup($groupId);
        // Delete by primary key will remove the object, so we don't need to clear `-by-identifier` variant here.
        $this->cache->deleteMulti(['ez-content-type-group-' . $groupId, 'ez-content-type-group-list']);
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        $group = $this->cache->get('ez-content-type-group-' . $groupId);
        if ($group === null) {
            $group = $this->innerHandler->loadGroup($groupId);
            $this->storeGroupCache([$group]);
        }

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroups(array $groupIds)
    {
        $groups = $missingIds = [];
        foreach ($groupIds as $groupId) {
            if ($group = $this->cache->get('ez-content-type-group-' . $groupId)) {
                $groups[$groupId] = $group;
            } else {
                $missingIds[] = $groupId;
            }
        }

        if (!empty($missingIds)) {
            $loaded = $this->innerHandler->loadGroups($missingIds);
            $this->storeGroupCache($loaded);
            /** @noinspection AdditionOperationOnArraysInspection */
            $groups += $loaded;
        }

        return $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroupByIdentifier($identifier)
    {
        $group = $this->cache->get('ez-content-type-group-' . $identifier . '-by-identifier');
        if ($group === null) {
            $group = $this->innerHandler->loadGroupByIdentifier($identifier);
            $this->storeGroupCache([$group]);
        }

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups()
    {
        $groups = $this->cache->get('ez-content-type-group-list');
        if ($groups === null) {
            $groups = $this->innerHandler->loadAllGroups();
            $this->storeGroupCache($groups, 'ez-content-type-group-list');
        }

        return $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypes($groupId, $status = Type::STATUS_DEFINED)
    {
        if ($status !== Type::STATUS_DEFINED) {
            return $this->innerHandler->loadContentTypes($groupId, $status);
        }

        $types = $this->cache->get('ez-content-type-list-by-group-' . $groupId);
        if ($types === null) {
            $types = $this->innerHandler->loadContentTypes($groupId, $status);
            $this->storeTypeCache($types, 'ez-content-type-list-by-group-' . $groupId);
        }

        return $types;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeList(array $contentTypeIds): array
    {
        $contentTypes = $missingIds = [];
        foreach ($contentTypeIds as $contentTypeId) {
            if ($contentType = $this->cache->get('ez-content-type-' . $contentTypeId . '-' . Type::STATUS_DEFINED)) {
                $contentTypes[$contentTypeId] = $contentType;
            } else {
                $missingIds[] = $contentTypeId;
            }
        }

        if (!empty($missingIds)) {
            $loaded = $this->innerHandler->loadContentTypeList($missingIds);
            $this->storeTypeCache($loaded);
            /** @noinspection AdditionOperationOnArraysInspection */
            $contentTypes += $loaded;
        }

        return $contentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function load($contentTypeId, $status = Type::STATUS_DEFINED)
    {
        $contentType = $this->cache->get('ez-content-type-' . $contentTypeId . '-' . $status);
        if ($contentType === null) {
            $contentType = $this->innerHandler->load($contentTypeId, $status);
            $this->storeTypeCache([$contentType]);
        }

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier)
    {
        $contentType = $this->cache->get('ez-content-type-' . $identifier . '-by-identifier');
        if ($contentType === null) {
            $contentType = $this->innerHandler->loadByIdentifier($identifier);
            $this->storeTypeCache([$contentType]);
        }

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId)
    {
        $contentType = $this->cache->get('ez-content-type-' . $remoteId . '-by-remote');
        if ($contentType === null) {
            $contentType = $this->innerHandler->loadByRemoteId($remoteId);
            $this->storeTypeCache([$contentType]);
        }

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $createStruct)
    {
        $contentType = $this->innerHandler->create($createStruct);
        // Don't store as FieldTypeConstraints is not setup fully here from Legacy SE side
        $this->deleteTypeCache($contentType->id, $contentType->status);

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function update($typeId, $status, UpdateStruct $contentType)
    {
        $contentType = $this->innerHandler->update($typeId, $status, $contentType);
        $this->storeTypeCache([$contentType]);

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($contentTypeId, $status)
    {
        $this->innerHandler->delete($contentTypeId, $status);
        $this->deleteTypeCache($contentTypeId, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function createDraft($modifierId, $contentTypeId)
    {
        $contentType = $this->innerHandler->createDraft($modifierId, $contentTypeId);
        // Don't store as FieldTypeConstraints is not setup fully here from Legacy SE side
        $this->deleteTypeCache($contentType->id, $contentType->status);

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($userId, $contentTypeId, $status)
    {
        $contentType = $this->innerHandler->copy($userId, $contentTypeId, $status);
        $this->storeTypeCache([$contentType]);

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function unlink($groupId, $contentTypeId, $status)
    {
        $keys = ['ez-content-type-' . $contentTypeId . '-' . $status];
        if ($status === Type::STATUS_DEFINED) {
            $keys[] = 'ez-content-type-list-by-group-' . $groupId;
        }
        $this->cache->deleteMulti($keys);

        return $this->innerHandler->unlink($groupId, $contentTypeId, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function link($groupId, $contentTypeId, $status)
    {
        $keys = ['ez-content-type-' . $contentTypeId . '-' . $status];
        if ($status === Type::STATUS_DEFINED) {
            $keys[] = 'ez-content-type-list-by-group-' . $groupId;
        }
        $this->cache->deleteMulti($keys);

        return $this->innerHandler->link($groupId, $contentTypeId, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($id, $status)
    {
        return $this->innerHandler->getFieldDefinition($id, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentCount($contentTypeId)
    {
        return $this->innerHandler->getContentCount($contentTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldDefinition($contentTypeId, $status, FieldDefinition $fieldDefinition)
    {
        $this->deleteTypeCache($contentTypeId, $status);

        return $this->innerHandler->addFieldDefinition($contentTypeId, $status, $fieldDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFieldDefinition($contentTypeId, $status, $fieldDefinitionId)
    {
        $this->deleteTypeCache($contentTypeId, $status);

        return $this->innerHandler->removeFieldDefinition($contentTypeId, $status, $fieldDefinitionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateFieldDefinition($contentTypeId, $status, FieldDefinition $fieldDefinition)
    {
        $this->deleteTypeCache($contentTypeId, $status);

        return $this->innerHandler->updateFieldDefinition($contentTypeId, $status, $fieldDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function publish($contentTypeId)
    {
        $this->clearCache();

        return $this->innerHandler->publish($contentTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableFieldMap()
    {
        $map = $this->cache->get('ez-content-type-field-map');
        if ($map === null) {
            $map = $this->innerHandler->getSearchableFieldMap();
            $this->cache->setMulti(
                $map,
                static function () { return []; },
                'ez-content-type-field-map'
            );
        }

        return $map;
    }

    /**
     * {@inheritdoc}
     */
    public function removeContentTypeTranslation(int $contentTypeId, string $languageCode): Type
    {
        $this->clearCache();

        return $this->innerHandler->removeContentTypeTranslation($contentTypeId, $languageCode);
    }

    /**
     * Clear internal caches.
     */
    public function clearCache()
    {
        $this->cache->clear();
    }

    protected function deleteTypeCache(int $contentTypeId, int $status = Type::STATUS_DEFINED): void
    {
        if ($status !== Type::STATUS_DEFINED) {
            // Delete by primary key will remove the object, so we don't need to clear other variants here.
            $this->cache->deleteMulti(['ez-content-type-' . $contentTypeId . '-' . $status, 'ez-content-type-field-map']);
        } else {
            // We don't know group id in order to clear relevant "ez-content-type-list-by-group-$groupId".
            $this->cache->clear();
        }
    }

    protected function storeTypeCache(array $types, string $listIndex = null): void
    {
        $this->cache->setMulti(
            $types,
            static function (Type $type) {
                if ($type->status !== Type::STATUS_DEFINED) {
                    return ['ez-content-type-' . $type->id . '-' . $type->status];
                }

                return [
                    'ez-content-type-' . $type->id . '-' . $type->status,
                    'ez-content-type-' . $type->identifier . '-by-identifier',
                    'ez-content-type-' . $type->remoteId . '-by-remote',
                ];
            },
            $listIndex
        );

        $this->cache->deleteMulti(['ez-content-type-field-map']);
    }

    protected function storeGroupCache(array $groups, string $listIndex = null): void
    {
        $this->cache->setMulti(
            $groups,
            static function (Group $group) {
                return [
                    'ez-content-type-group-' . $group->id,
                    'ez-content-type-group-' . $group->identifier . '-by-identifier',
                ];
            },
            $listIndex
        );
    }

    public function deleteByUserAndStatus(int $userId, int $status): void
    {
        $this->innerHandler->deleteByUserAndStatus($userId, $status);
    }
}
