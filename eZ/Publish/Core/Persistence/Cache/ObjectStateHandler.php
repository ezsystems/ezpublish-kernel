<?php

/**
 * File containing the ObjectStateHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
 */
class ObjectStateHandler extends AbstractHandler implements ObjectStateHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createGroup(InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $input));
        $group = $this->persistenceHandler->objectStateHandler()->createGroup($input);

        $this->cache->deleteItem('ez-state-group-all');

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        $cacheItem = $this->cache->getItem('ez-state-group-' . $groupId);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('groupId' => $groupId));
        $group = $this->persistenceHandler->objectStateHandler()->loadGroup($groupId);

        $cacheItem->set($group);
        $cacheItem->tag(['state-group-' . $group->id]);
        $this->cache->save($cacheItem);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroupByIdentifier($identifier)
    {
        $cacheItem = $this->cache->getItem('ez-state-group-' . $identifier . '-by-identifier');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('groupId' => $identifier));
        $group = $this->persistenceHandler->objectStateHandler()->loadGroupByIdentifier($identifier);

        $cacheItem->set($group);
        $cacheItem->tag(['state-group-' . $group->id]);
        $this->cache->save($cacheItem);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups($offset = 0, $limit = -1)
    {
        $cacheItem = $this->cache->getItem('ez-state-group-all');
        if ($cacheItem->isHit()) {
            return array_slice($cacheItem->get(), $offset, $limit > -1 ? $limit : null);
        }

        $this->logger->logCall(__METHOD__, array('offset' => $offset, 'limit' => $limit));
        $stateGroups = $this->persistenceHandler->objectStateHandler()->loadAllGroups(0, -1);

        $cacheItem->set($stateGroups);
        $cacheTags = [];
        foreach ($stateGroups as $group) {
            $cacheTags[] = 'state-group-' . $group->id;
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return array_slice($stateGroups, $offset, $limit > -1 ? $limit : null);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStates($groupId)
    {
        $cacheItem = $this->cache->getItem('ez-state-list-' . $groupId . '-by-group');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('groupId' => $groupId));
        $objectStates = $this->persistenceHandler->objectStateHandler()->loadObjectStates($groupId);

        $cacheItem->set($objectStates);
        $cacheTags = ['state-group-' . $groupId];
        foreach ($objectStates as $state) {
            $cacheTags[] = 'state-' . $state->id;
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $objectStates;
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('groupId' => $groupId, 'struct' => $input));
        $return = $this->persistenceHandler->objectStateHandler()->updateGroup($groupId, $input);

        $this->cache->invalidateTags(['state-group-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, array('groupId' => $groupId));
        $return = $this->persistenceHandler->objectStateHandler()->deleteGroup($groupId);

        $this->cache->invalidateTags(['state-group-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function create($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('groupId' => $groupId, 'struct' => $input));
        $return = $this->persistenceHandler->objectStateHandler()->create($groupId, $input);

        $this->cache->deleteItem('ez-state-list-by-group-' . $groupId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($stateId)
    {
        $cacheItem = $this->cache->getItem('ez-state-' . $stateId);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('stateId' => $stateId));
        $objectState = $this->persistenceHandler->objectStateHandler()->load($stateId);

        $cacheItem->set($objectState);
        $cacheItem->tag(['state-' . $objectState->id, 'state-group-' . $objectState->groupId]);
        $this->cache->save($cacheItem);

        return $objectState;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier, $groupId)
    {
        $cacheItem = $this->cache->getItem('ez-state-identifier-' . $identifier . '-by-group-' . $groupId);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('identifier' => $identifier, 'groupId' => $groupId));
        $objectState = $this->persistenceHandler->objectStateHandler()->loadByIdentifier($identifier, $groupId);

        $cacheItem->set($objectState);
        $cacheItem->tag(['state-' . $objectState->id, 'state-group-' . $objectState->groupId]);
        $this->cache->save($cacheItem);

        return $objectState;
    }

    /**
     * {@inheritdoc}
     */
    public function update($stateId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId, 'struct' => $input));
        $return = $this->persistenceHandler->objectStateHandler()->update($stateId, $input);

        $this->cache->invalidateTags(['state-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($stateId, $priority)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId, 'priority' => $priority));
        $return = $this->persistenceHandler->objectStateHandler()->setPriority($stateId, $priority);

        $this->cache->invalidateTags(['state-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($stateId)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId));
        $return = $this->persistenceHandler->objectStateHandler()->delete($stateId);

        $this->cache->invalidateTags(['state-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentState($contentId, $groupId, $stateId)
    {
        $this->logger->logCall(__METHOD__, array('contentId' => $contentId, 'groupId' => $groupId, 'stateId' => $stateId));
        $return = $this->persistenceHandler->objectStateHandler()->setContentState($contentId, $groupId, $stateId);

        $this->cache->deleteItem('ez-state-by-group-' . $groupId . '-on-content-' . $contentId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentState($contentId, $stateGroupId)
    {
        $cacheItem = $this->cache->getItem('ez-state-by-group-' . $stateGroupId . '-on-content-' . $contentId);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('contentId' => $contentId, 'stateGroupId' => $stateGroupId));
        $contentState = $this->persistenceHandler->objectStateHandler()->getContentState($contentId, $stateGroupId);

        $cacheItem->set($contentState);
        $cacheItem->tag(['state-' . $contentState->id, 'content-' . $contentId]);
        $this->cache->save($cacheItem);

        return $contentState;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentCount($stateId)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId));

        return $this->persistenceHandler->objectStateHandler()->getContentCount($stateId);
    }
}
