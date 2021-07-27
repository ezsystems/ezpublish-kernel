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

        $this->cache->deleteItem(TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP_ALL);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        return $this->getCacheValue(
            (int) $groupId,
            TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP . '-',
            function (int $groupId): Group {
                $this->logger->logCall(__METHOD__, ['groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadGroup($groupId);
            },
            static function () use ($groupId): array {
                return [TagIdentifiers::STATE_GROUP . '-' . (int) $groupId];
            },
            static function () use ($groupId) {
                return [TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP . '-' . (int) $groupId];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroupByIdentifier($identifier)
    {
        $escapedIdentifier = $this->escapeForCacheKey($identifier);

        return $this->getCacheValue(
            $identifier,
            TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP . '-',
            function (string $identifier): Group {
                $this->logger->logCall(__METHOD__, ['groupId' => $identifier]);

                return $this->persistenceHandler->objectStateHandler()->loadGroupByIdentifier($identifier);
            },
            static function (Group $group): array {
                return [TagIdentifiers::STATE_GROUP . '-' . $group->id];
            },
            static function (Group $group) use ($escapedIdentifier): array {
                return [
                    TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP . '-' . $escapedIdentifier . TagIdentifiers::BY_IDENTIFIER_SUFFIX,
                    TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP . '-' . $group->id
                ];
            },
            TagIdentifiers::BY_IDENTIFIER_SUFFIX
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups($offset = 0, $limit = -1)
    {
        $stateGroups = $this->getListCacheValue(
            TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP_ALL,
            function () use ($offset, $limit): array {
                $this->logger->logCall(__METHOD__, ['offset' => (int) $offset, 'limit' => (int) $limit]);

                return $this->persistenceHandler->objectStateHandler()->loadAllGroups(0, -1);
            },
            static function (Group $group): array {
                return [TagIdentifiers::STATE_GROUP . '-' . $group->id];
            },
            static function (Group $group): array {
                return [
                    TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP . '-' . $group->id,
                    TagIdentifiers::PREFIX . TagIdentifiers::STATE_GROUP . '-' . $group->id . TagIdentifiers::BY_IDENTIFIER_SUFFIX,
                ];
            }
        );

        return \array_slice($stateGroups, $offset, $limit > -1 ? $limit : null);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStates($groupId)
    {
        return $this->getCacheValue(
            $groupId,
            TagIdentifiers::PREFIX . TagIdentifiers::STATE_LIST_BY_GROUP . '-',
            function (int $groupId): array {
                $this->logger->logCall(__METHOD__, ['groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadObjectStates($groupId);
            },
            static function (array $objectStates) use ($groupId): array {
                $cacheTags = [];
                $cacheTags[] = TagIdentifiers::STATE_GROUP . '-' . (int) $groupId;
                foreach ($objectStates as $state) {
                    $cacheTags[] = TagIdentifiers::STATE . '-' . $state->id;
                }

                return $cacheTags;
            },
            static function () use ($groupId): array {
                return [TagIdentifiers::PREFIX . TagIdentifiers::STATE_LIST_BY_GROUP . '-' . (int) $groupId];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->updateGroup($groupId, $input);

        $this->cache->invalidateTags([TagIdentifiers::STATE_GROUP . '-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId]);
        $return = $this->persistenceHandler->objectStateHandler()->deleteGroup($groupId);

        $this->cache->invalidateTags([TagIdentifiers::STATE_GROUP . '-' . $groupId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function create($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->create($groupId, $input);

        $this->cache->deleteItem(TagIdentifiers::PREFIX . TagIdentifiers::STATE_LIST_BY_GROUP . '-' . $groupId);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($stateId)
    {
        return $this->getCacheValue(
            (int) $stateId,
            TagIdentifiers::PREFIX . TagIdentifiers::STATE . '-',
            function (int $stateId): ObjectState {
                $this->logger->logCall(__METHOD__, ['stateId' => $stateId]);

                return $this->persistenceHandler->objectStateHandler()->load($stateId);
            },
            static function (ObjectState $objectState): array {
                return [
                    TagIdentifiers::STATE . '-' . $objectState->id,
                    TagIdentifiers::STATE_GROUP . '-' . $objectState->groupId
                ];
            },
            static function () use ($stateId): array {
                return [TagIdentifiers::PREFIX . TagIdentifiers::STATE . '-' . (int) $stateId];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier, $groupId)
    {
        $escapedIdentifier = $this->escapeForCacheKey($identifier);

        return $this->getCacheValue(
            $identifier,
            TagIdentifiers::PREFIX . TagIdentifiers::STATE_IDENTIFIER . '-',
            function (string $identifier) use ($groupId): ObjectState {
                $this->logger->logCall(__METHOD__, ['identifier' => $identifier, 'groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadByIdentifier($identifier, (int) $groupId);
            },
            static function (ObjectState $objectState): array {
                return [
                    TagIdentifiers::STATE . '-' . $objectState->id,
                    TagIdentifiers::STATE_GROUP . '-' . $objectState->groupId
                ];
            },
            static function () use ($escapedIdentifier, $groupId): array {
                return [
                    TagIdentifiers::PREFIX . 
                    TagIdentifiers::STATE_IDENTIFIER . '-' . 
                    $escapedIdentifier .
                    TagIdentifiers::BY_GROUP_SUFFIX . '-' .
                    (int) $groupId
                ];
            },
            TagIdentifiers::BY_GROUP_SUFFIX . '-' . (int) $groupId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update($stateId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->update($stateId, $input);

        $this->cache->invalidateTags([TagIdentifiers::STATE . '-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($stateId, $priority)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId, 'priority' => $priority]);
        $return = $this->persistenceHandler->objectStateHandler()->setPriority($stateId, $priority);

        $this->cache->invalidateTags([TagIdentifiers::STATE . '-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($stateId)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId]);
        $return = $this->persistenceHandler->objectStateHandler()->delete($stateId);

        $this->cache->invalidateTags([TagIdentifiers::STATE . '-' . $stateId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentState($contentId, $groupId, $stateId)
    {
        $this->logger->logCall(__METHOD__, ['contentId' => $contentId, 'groupId' => $groupId, 'stateId' => $stateId]);
        $return = $this->persistenceHandler->objectStateHandler()->setContentState($contentId, $groupId, $stateId);

        $this->cache->deleteItem(
            TagIdentifiers::PREFIX . 
            TagIdentifiers::STATE_BY_GROUP . '-' . 
            $groupId .
            TagIdentifiers::ON_CONTENT_SUFFIX . '-' .
            $contentId
        );

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentState($contentId, $stateGroupId)
    {
        return $this->getCacheValue(
            (int) $stateGroupId,
            TagIdentifiers::PREFIX . TagIdentifiers::STATE_BY_GROUP . '-',
            function (int $stateGroupId) use ($contentId): ObjectState {
                $this->logger->logCall(__METHOD__, ['contentId' => (int) $contentId, 'stateGroupId' => $stateGroupId]);

                return $this->persistenceHandler->objectStateHandler()->getContentState((int) $contentId, $stateGroupId);
            },
            static function (ObjectState $contentState) use ($contentId): array {
                return [
                    TagIdentifiers::STATE . '-' . $contentState->id,
                    TagIdentifiers::CONTENT . '-' . (int) $contentId
                ];
            },
            static function () use ($contentId, $stateGroupId): array {
                return [
                    TagIdentifiers::PREFIX .
                    TagIdentifiers::STATE_BY_GROUP . '-' .
                    (int) $stateGroupId .
                    TagIdentifiers::ON_CONTENT_SUFFIX . '-' .
                    (int) $contentId
                ];
            },
            TagIdentifiers::ON_CONTENT_SUFFIX . '-' . $contentId
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
