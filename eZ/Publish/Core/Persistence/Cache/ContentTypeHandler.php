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
        $group = $this->persistenceHandler->contentTypeHandler()->createGroup($struct);

        $this->cache->getItem('contentTypeGroup', $group->id)->set($group)->save();

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup(GroupUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        $this->cache
            ->getItem('contentTypeGroup', $struct->id)
            ->set($group = $this->persistenceHandler->contentTypeHandler()->updateGroup($struct))
            ->save();

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, array('group' => $groupId));
        $return = $this->persistenceHandler->contentTypeHandler()->deleteGroup($groupId);

        $this->cache->clear('contentTypeGroup', $groupId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        $cache = $this->cache->getItem('contentTypeGroup', $groupId);
        $group = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('group' => $groupId));
            $cache->set($group = $this->persistenceHandler->contentTypeHandler()->loadGroup($groupId))->save();
        }

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroupByIdentifier($identifier)
    {
        $this->logger->logCall(__METHOD__, array('group' => $identifier));

        return $this->persistenceHandler->contentTypeHandler()->loadGroupByIdentifier($identifier);
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
        $this->logger->logCall(__METHOD__, array('group' => $groupId, 'status' => $status));

        return $this->persistenceHandler->contentTypeHandler()->loadContentTypes($groupId, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function load($typeId, $status = Type::STATUS_DEFINED)
    {
        if ($status !== Type::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status));

            return $this->persistenceHandler->contentTypeHandler()->load($typeId, $status);
        }

        // Get cache for published content types
        $cache = $this->cache->getItem('contentType', $typeId);
        $type = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status));
            $cache->set($type = $this->persistenceHandler->contentTypeHandler()->load($typeId, $status))->save();
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier)
    {
        // Get identifier to id cache if there is one (avoids caching an object several times)
        $cache = $this->cache->getItem('contentType', 'identifier', $identifier);
        $typeId = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('type' => $identifier));
            $type = $this->persistenceHandler->contentTypeHandler()->loadByIdentifier($identifier);
            $cache->set($type->id)->save();
            // Warm contentType cache in case it's not set
            $this->cache->getItem('contentType', $type->id)->set($type)->save();
        } else {
            // Reuse load() if we have id (it should be cached anyway)
            $type = $this->load($typeId);
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByRemoteId($remoteId)
    {
        $this->logger->logCall(__METHOD__, array('type' => $remoteId));

        return $this->persistenceHandler->contentTypeHandler()->loadByRemoteId($remoteId);
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $contentType)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $contentType));
        $type = $this->persistenceHandler->contentTypeHandler()->create($contentType);

        if ($type->status === Type::STATUS_DEFINED) {
            // Warm cache
            $this->cache->getItem('contentType', $type->id)->set($type)->save();
            $this->cache->getItem('contentType', 'identifier', $type->identifier)->set($type->id)->save();
            $this->cache->clear('searchableFieldMap');
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function update($typeId, $status, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId, 'status' => $status, 'struct' => $struct));
        if ($status !== Type::STATUS_DEFINED) {
            return $this->persistenceHandler->contentTypeHandler()->update($typeId, $status, $struct);
        }

        // Warm cache
        $this->cache
            ->getItem('contentType', $typeId)
            ->set($type = $this->persistenceHandler->contentTypeHandler()->update($typeId, $status, $struct))
            ->save();

        // Clear identifier cache in case it was changed before warming the new one
        $this->cache->clear('contentType', 'identifier');
        $this->cache->clear('searchableFieldMap');
        $this->cache->getItem('contentType', 'identifier', $type->identifier)->set($typeId)->save();

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
            // Clear type cache and all identifier cache (as we don't know the identifier)
            $this->cache->clear('contentType', $typeId);
            $this->cache->clear('contentType', 'identifier');
            $this->cache->clear('searchableFieldMap');
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function createDraft($modifierId, $typeId)
    {
        $this->logger->logCall(__METHOD__, array('modifier' => $modifierId, 'type' => $typeId));

        return $this->persistenceHandler->contentTypeHandler()->createDraft($modifierId, $typeId);
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
            $this->cache->clear('contentType', $typeId);
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
            $this->cache->clear('contentType', $typeId);
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
            $this->cache->clear('contentType', $typeId);
            $this->cache->clear('searchableFieldMap');
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
            $this->cache->clear('contentType', $typeId);
            $this->cache->clear('searchableFieldMap');
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
            $this->cache->clear('contentType', $typeId);
            $this->cache->clear('searchableFieldMap');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish($typeId)
    {
        $this->logger->logCall(__METHOD__, array('type' => $typeId));
        $this->persistenceHandler->contentTypeHandler()->publish($typeId);

        // Clear type cache and all identifier cache (as we don't know the identifier)
        $this->cache->clear('contentType', $typeId);
        $this->cache->clear('contentType', 'identifier');
        $this->cache->clear('searchableFieldMap');

        // clear content cache
        $this->cache->clear('content');//TIMBER! (possible content changes)
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableFieldMap()
    {
        $cache = $this->cache->getItem('searchableFieldMap');

        $fieldMap = $cache->get();

        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__);
            $fieldMap = $this->persistenceHandler->contentTypeHandler()->getSearchableFieldMap();
            $cache->set($fieldMap)->save();
        }

        return $fieldMap;
    }
}
