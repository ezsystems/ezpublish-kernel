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
 * @see eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
 */
class ObjectStateHandler extends AbstractHandler implements ObjectStateHandlerInterface
{
    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::createGroup
     */
    public function createGroup(InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $input));
        $group = $this->persistenceHandler->objectStateHandler()->createGroup($input);

        $this->cache->clear('objectstategroup', 'all');
        $this->cache->getItem('objectstategroup', $group->id)->set($group)->save();

        return $group;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadGroup
     */
    public function loadGroup($groupId)
    {
        $cache = $this->cache->getItem('objectstategroup', $groupId);
        $group = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('groupId' => $groupId));
            $cache->set($group = $this->persistenceHandler->objectStateHandler()->loadGroup($groupId))->save();
        }

        return $group;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadGroupByIdentifier
     */
    public function loadGroupByIdentifier($identifier)
    {
        $this->logger->logCall(__METHOD__, array('identifier' => $identifier));

        return $this->persistenceHandler->objectStateHandler()->loadGroupByIdentifier($identifier);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadAllGroups
     */
    public function loadAllGroups($offset = 0, $limit = -1)
    {
        // Method caches all state groups in cache only uses offset / limit to slice the cached result
        $cache = $this->cache->getItem('objectstategroup', 'all');
        $stateGroups = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('offset' => $offset, 'limit' => $limit));
            $stateGroups = $this->persistenceHandler->objectStateHandler()->loadAllGroups(0, -1);
            $cache->set($stateGroups)->save();
            $stateGroups = array_slice($stateGroups, $offset, $limit > -1 ?: null);
        } else {
            $stateGroups = array_slice($stateGroups, $offset, $limit > -1 ?: null);
            // BC for updates to 6.7LTS installs where cache contains ID's and not objects
            // @todo Remove in later branches
            foreach ($stateGroups as $key => $stateGroup) {
                if (is_numeric($stateGroup)) {
                    $stateGroups[$key] = $this->loadGroup($stateGroup);
                }
            }
        }

        return $stateGroups;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadObjectStates
     */
    public function loadObjectStates($groupId)
    {
        $cache = $this->cache->getItem('objectstate', 'byGroup', $groupId);
        $objectStates = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('groupId' => $groupId));
            $objectStates = $this->persistenceHandler->objectStateHandler()->loadObjectStates($groupId);
            $cache->set($objectStates)->save();
        } else {
            // BC for updates to 6.7LTS installs where cache contains ID's and not objects
            // @todo Remove in later branches
            foreach ($objectStates as $key => $state) {
                if (is_numeric($state)) {
                    $objectStates[$key] = $this->load($state);
                }
            }
        }

        return $objectStates;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::updateGroup
     */
    public function updateGroup($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('groupId' => $groupId, 'struct' => $input));
        $return = $this->persistenceHandler->objectStateHandler()->updateGroup($groupId, $input);

        $this->cache->clear('objectstategroup', $groupId);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::deleteGroup
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, array('groupId' => $groupId));
        $return = $this->persistenceHandler->objectStateHandler()->deleteGroup($groupId);

        $this->cache->clear('objectstategroup', 'all');
        $this->cache->clear('objectstategroup', $groupId);
        $this->cache->clear('objectstate', 'byGroup', $groupId);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::create
     */
    public function create($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('groupId' => $groupId, 'struct' => $input));
        $return = $this->persistenceHandler->objectStateHandler()->create($groupId, $input);

        $this->cache->clear('objectstate', 'byGroup', $groupId);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::load
     */
    public function load($stateId)
    {
        $cache = $this->cache->getItem('objectstate', $stateId);
        $objectState = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('stateId' => $stateId));
            $cache->set($objectState = $this->persistenceHandler->objectStateHandler()->load($stateId))->save();
        }

        return $objectState;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadByIdentifier
     */
    public function loadByIdentifier($identifier, $groupId)
    {
        $this->logger->logCall(__METHOD__, array('identifier' => $identifier, 'groupId' => $groupId));

        return $this->persistenceHandler->objectStateHandler()->loadByIdentifier($identifier, $groupId);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::update
     */
    public function update($stateId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId, 'struct' => $input));
        $return = $this->persistenceHandler->objectStateHandler()->update($stateId, $input);

        $this->cache->clear('objectstate', $stateId);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::setPriority
     */
    public function setPriority($stateId, $priority)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId, 'priority' => $priority));
        $return = $this->persistenceHandler->objectStateHandler()->setPriority($stateId, $priority);

        $this->cache->clear('objectstate', $stateId);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::delete
     */
    public function delete($stateId)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId));
        $return = $this->persistenceHandler->objectStateHandler()->delete($stateId);

        $this->cache->clear('objectstate', $stateId);
        $this->cache->clear('objectstate', 'byGroup'); // TIMBER!

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::setContentState
     */
    public function setContentState($contentId, $groupId, $stateId)
    {
        $this->logger->logCall(__METHOD__, array('contentId' => $contentId, 'groupId' => $groupId, 'stateId' => $stateId));
        $return = $this->persistenceHandler->objectStateHandler()->setContentState($contentId, $groupId, $stateId);

        $this->cache->clear('objectstate', 'byContent', $contentId, $groupId);

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::getContentState
     */
    public function getContentState($contentId, $stateGroupId)
    {
        $cache = $this->cache->getItem('objectstate', 'byContent', $contentId, $stateGroupId);
        $stateId = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('contentId' => $contentId, 'stateGroupId' => $stateGroupId));

            $contentState = $this->persistenceHandler->objectStateHandler()->getContentState($contentId, $stateGroupId);
            $cache->set($contentState->id)->save();

            return $contentState;
        }

        return $this->load($stateId);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::getContentCount
     *
     * @todo cache results
     */
    public function getContentCount($stateId)
    {
        $this->logger->logCall(__METHOD__, array('stateId' => $stateId));

        return $this->persistenceHandler->objectStateHandler()->getContentCount($stateId);
    }
}
