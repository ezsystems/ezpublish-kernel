<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
 */
class ObjectStateHandler extends AbstractInMemoryPersistenceHandler implements ObjectStateHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createGroup(InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $input]);
        $group = $this->persistenceHandler->objectStateHandler()->createGroup($input);

        $this->cache->deleteItem('ez-state-group-all');

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        return $this->getCacheValue(
            (int) $groupId,
            'ez-state-group-',
            function (int $groupId) {
                return $this->persistenceHandler->objectStateHandler()->loadGroup($groupId);
            },
            static function () use ($groupId) {
                return ['state-group-' . (int) $groupId];
            },
            static function () use ($groupId) {
                return ['ez-state-group-' . (int) $groupId];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroupByIdentifier($identifier)
    {
        $identifier = $this->escapeForCacheKey($identifier);

        return $this->getCacheValue(
            $identifier,
            'ez-state-group-',
            function ($identifier) {
                return $this->persistenceHandler->objectStateHandler()->loadGroupByIdentifier($identifier);
            },
            static function (Group $group) {
                return ['state-group-' . $group->id];
            },
            static function () use ($identifier) {
                return ['ez-state-group-' . $identifier . '-by-identifier'];
            },
            '-by-identifier'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups($offset = 0, $limit = -1)
    {
        $stateGroups = $this->getCacheValue(
            '',
            'ez-state-group-all',
            function () {
                return $this->persistenceHandler->objectStateHandler()->loadAllGroups(0, -1);
            },
            static function (array $stateGroups) {
                $cacheTags = [];
                foreach ($stateGroups as $group) {
                    $cacheTags[] = 'state-group-' . $group->id;
                }

                return $cacheTags;
            },
            static function () {
                return ['ez-state-group-all'];
            }
        );

        return \array_slice($stateGroups, $offset, $limit > -1 ? $limit : null);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStates($groupId)
    {
        $objectStates = $this->getCacheValue(
            $groupId,
            'ez-state-list-by-group-',
            function ($groupId) {
                return $this->persistenceHandler->objectStateHandler()->loadObjectStates($groupId);
            },
            static function (array $objectStates) use ($groupId) {
                $cacheTags = [];
                $cacheTags[] = 'state-group-' . (int) $groupId;
                foreach ($objectStates as $state) {
                    $cacheTags[] = 'state-' . $state->id;
                }

                return $cacheTags;
            },
            static function () use ($groupId) {
                return ['ez-state-list-by-group-' . (int) $groupId];
            }
        );

        return $objectStates;
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->updateGroup($groupId, $input);

        $this->cache->invalidateTags(['state-group-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId]);
        $return = $this->persistenceHandler->objectStateHandler()->deleteGroup($groupId);

        $this->cache->invalidateTags(['state-group-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function create($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->create($groupId, $input);

        $this->cache->deleteItem('ez-state-list-by-group-' . $groupId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($stateId)
    {
        $objectState = $this->getCacheValue(
            (int) $stateId,
            'ez-state-',
            function ($stateId) {
                return $this->persistenceHandler->objectStateHandler()->load((int) $stateId);
            },
            static function (ObjectState $objectState) {
                return ['state-' . $objectState->id, 'state-group-' . $objectState->groupId];
            },
            static function () use ($stateId) {
                return ['ez-state-' . (int) $stateId];
            }
        );

        return $objectState;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier, $groupId)
    {
        $identifier = $this->escapeForCacheKey($identifier);

        return $this->getCacheValue(
            $identifier,
            'ez-state-identifier-',
            function ($identifier) use ($groupId) {
                return $this->persistenceHandler->objectStateHandler()->loadByIdentifier($identifier, (int) $groupId);
            },
            static function (ObjectState $objectState) {
                return ['state-' . $objectState->id, 'state-group-' . $objectState->groupId];
            },
            static function () use ($identifier, $groupId) {
                return ['ez-state-identifier-' . $identifier . '-by-group-' . (int) $groupId];
            },
            '-by-group-' . (int) $groupId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update($stateId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->update($stateId, $input);

        $this->cache->invalidateTags(['state-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($stateId, $priority)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId, 'priority' => $priority]);
        $return = $this->persistenceHandler->objectStateHandler()->setPriority($stateId, $priority);

        $this->cache->invalidateTags(['state-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($stateId)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId]);
        $return = $this->persistenceHandler->objectStateHandler()->delete($stateId);

        $this->cache->invalidateTags(['state-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentState($contentId, $groupId, $stateId)
    {
        $this->logger->logCall(__METHOD__, ['contentId' => $contentId, 'groupId' => $groupId, 'stateId' => $stateId]);
        $return = $this->persistenceHandler->objectStateHandler()->setContentState($contentId, $groupId, $stateId);

        $this->cache->deleteItem('ez-state-by-group-' . $groupId . '-on-content-' . $contentId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentState($contentId, $stateGroupId)
    {
        return $this->getCacheValue(
            (int) $stateGroupId,
            'ez-state-by-group-',
            function ($stateGroupId) use ($contentId) {
                return $this->persistenceHandler->objectStateHandler()->getContentState((int) $contentId, (int) $stateGroupId);
            },
            static function (ObjectState $contentState) use ($contentId) {
                return ['state-' . $contentState->id, 'content-' . (int) $contentId];
            },
            static function () use ($contentId, $stateGroupId) {
                return ['ez-state-by-group-' . (int) $stateGroupId . '-on-content-' . (int) $contentId];
            },
            '-on-content-' . $contentId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContentCount($stateId)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId]);

        return $this->persistenceHandler->objectStateHandler()->getContentCount($stateId);
    }
}
