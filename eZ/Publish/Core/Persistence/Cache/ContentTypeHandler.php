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
class ContentTypeHandler extends AbstractHandler implements ContentTypeHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createGroup(GroupCreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        return $this->persistenceHandler->contentTypeHandler()->createGroup($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup(GroupUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $group = $this->persistenceHandler->contentTypeHandler()->updateGroup($struct);

        $this->cache->invalidateTags(['type-group-' . $struct->id]);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, array('group' => $groupId));
        $return = $this->persistenceHandler->contentTypeHandler()->deleteGroup($groupId);

        $this->cache->invalidateTags(['type-group-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        $cacheItem = $this->cache->getItem('ez-content-type-group-' . $groupId);
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['group' => $groupId]);

            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('group' => $groupId));
        $group = $this->persistenceHandler->contentTypeHandler()->loadGroup($groupId);

        $cacheItem->set($group);
        $cacheItem->tag('type-group-' . $group->id);
        $this->cache->save($cacheItem);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroups(array $groupIds)
    {
        return $this->getMultipleCacheItems(
            $groupIds,
            'ez-content-type-group-',
            function (array $cacheMissIds) {
                $this->logger->logCall(__CLASS__ . '::loadGroups', ['groups' => $cacheMissIds]);

                return $this->persistenceHandler->contentTypeHandler()->loadGroups($cacheMissIds);
            },
            function (Type\Group $group) {
                return ['type-group-' . $group->id];
            },
            function () use ($groupIds) {
                $this->logger->logCacheHit(__CLASS__ . '::loadGroups', ['groups' => $groupIds]);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroupByIdentifier($identifier)
    {
        $cacheItem = $this->cache->getItem('ez-content-type-group-' . $identifier . '-by-identifier');
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['group' => $identifier]);

            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('group' => $identifier));
        $group = $this->persistenceHandler->contentTypeHandler()->loadGroupByIdentifier($identifier);

        $cacheItem->set($group);
        $cacheItem->tag('type-group-' . $group->id);
        $this->cache->save($cacheItem);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->contentTypeHandler()->loadAllGroups();
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypes($groupId, $status = Type::STATUS_DEFINED)
    {
        if ($status !== Type::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, array('group' => $groupId, 'status' => $status));

            return $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);
        }

        $cacheItem = $this->cache->getItem('ez-content-type-list-by-group-' . $groupId);
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['group' => $groupId, 'status' => $status]);

            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('group' => $groupId, 'status' => $status));
        $types = $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);

        $cacheTags = ['type-group-' . $groupId];
        foreach ($types as $type) {
            $cacheTags[] = 'type-' . $type->id;
        }

        $cacheItem->set($types);
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $types;
    }

    /**
     * {@inheritdoc}
     */
    public function load($typeId, $status = Type::STATUS_DEFINED)
    {
        $cacheItem = $this->cache->getItem('ez-content-type-' . $typeId . '-' . $status);
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['type' => $typeId, 'status' => $status]);

            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status));
        $type = $this->persistenceHandler->contentTypeHandler()->load($typeId, $status);

        $cacheItem->set($type);
        $cacheItem->tag(['type-' . $type->id]);
        $this->cache->save($cacheItem);

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier)
    {
        $cacheItem = $this->cache->getItem('ez-content-type-' . $identifier . '-by-identifier');
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['type' => $identifier]);

            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('type' => $identifier));
        $type = $this->persistenceHandler->contentTypeHandler()->loadByIdentifier($identifier);

        $cacheItem->set($type);
        $cacheItem->tag(['type-' . $type->id]);
        $this->cache->save($cacheItem);

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId)
    {
        $cacheItem = $this->cache->getItem('ez-content-type-' . $remoteId . '-by-remote');
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['type' => $remoteId]);

            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('type' => $remoteId));
        $type = $this->persistenceHandler->contentTypeHandler()->loadByRemoteId($remoteId);

        $cacheItem->set($type);
        $cacheItem->tag(['type-' . $type->id]);
        $this->cache->save($cacheItem);

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        $type = $this->persistenceHandler->contentTypeHandler()->create($struct);

        // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
        foreach ($struct->groupIds as $groupId) {
            $this->cache->deleteItem('ez-content-type-list-by-group-' . $groupId);
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function update($typeId, $status, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status, 'struct' => $struct));
        $type = $this->persistenceHandler->contentTypeHandler()->update($typeId, $status, $struct);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . $status);
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($typeId, $status)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status));
        $return = $this->persistenceHandler->contentTypeHandler()->delete($typeId, $status);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . $status);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function createDraft($modifierId, $typeId)
    {
        $this->logger->logCall(__METHOD__, array('modifier' => $modifierId, 'type' => $typeId));
        $draft = $this->persistenceHandler->contentTypeHandler()->createDraft($modifierId, $typeId);

        $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . Type::STATUS_DRAFT);

        return $draft;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($userId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId, 'type' => $typeId, 'status' => $status));

        return $this->persistenceHandler->contentTypeHandler()->copy($userId, $typeId, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function unlink($groupId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, array('group' => $groupId, 'type' => $typeId, 'status' => $status));
        $return = $this->persistenceHandler->contentTypeHandler()->unlink($groupId, $typeId, $status);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId]);
        } else {
            $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . $status);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function link($groupId, $typeId, $status)
    {
        $this->logger->logCall(__METHOD__, array('group' => $groupId, 'type' => $typeId, 'status' => $status));
        $return = $this->persistenceHandler->contentTypeHandler()->link($groupId, $typeId, $status);

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId]);
            // Clear loadContentTypes() cache as we effetely add an item to it's collection here.
            $this->cache->deleteItem('ez-content-type-list-by-group-' . $groupId);
        } else {
            $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . $status);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($id, $status)
    {
        $this->logger->logCall(__METHOD__, array('field' => $id, 'status' => $status));

        return $this->persistenceHandler->contentTypeHandler()->getFieldDefinition($id, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentCount($contentTypeId)
    {
        $this->logger->logCall(__METHOD__, array('contentTypeId' => $contentTypeId));

        return $this->persistenceHandler->contentTypeHandler()->getContentCount($contentTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldDefinition($typeId, $status, FieldDefinition $struct)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status, 'struct' => $struct));
        $return = $this->persistenceHandler->contentTypeHandler()->addFieldDefinition(
            $typeId,
            $status,
            $struct
        );

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . $status);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFieldDefinition($typeId, $status, $fieldDefinitionId)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status, 'field' => $fieldDefinitionId));
        $this->persistenceHandler->contentTypeHandler()->removeFieldDefinition(
            $typeId,
            $status,
            $fieldDefinitionId
        );

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . $status);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateFieldDefinition($typeId, $status, FieldDefinition $struct)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status, 'struct' => $struct));
        $this->persistenceHandler->contentTypeHandler()->updateFieldDefinition(
            $typeId,
            $status,
            $struct
        );

        if ($status === Type::STATUS_DEFINED) {
            $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);
        } else {
            $this->cache->deleteItem('ez-content-type-' . $typeId . '-' . $status);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish($typeId)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId));
        $this->persistenceHandler->contentTypeHandler()->publish($typeId);

        // Clear type cache, map cache, and content cache which contains fields.
        $this->cache->invalidateTags(['type-' . $typeId, 'type-map', 'content-fields-type-' . $typeId]);

        // Clear Content Type Groups list cache
        $contentType = $this->load($typeId, Type::STATUS_DEFINED);
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
        $cacheItem = $this->cache->getItem('ez-content-type-field-map');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__);
        $fieldMap = $this->persistenceHandler->contentTypeHandler()->getSearchableFieldMap();

        $cacheItem->set($fieldMap);
        $cacheItem->tag(['type-map']);
        $this->cache->save($cacheItem);

        return $fieldMap;
    }
}
