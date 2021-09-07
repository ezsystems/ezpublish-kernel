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
    private const STATE_GROUP_ALL_IDENTIFIER = 'state_group_all';
    private const STATE_GROUP_IDENTIFIER = 'state_group';
    private const STATE_GROUP_WITH_ID_SUFFIX_IDENTIFIER = 'state_group_with_id_suffix';
    private const BY_IDENTIFIER_SUFFIX = 'by_identifier_suffix';
    private const STATE_IDENTIFIER = 'state';
    private const STATE_LIST_BY_GROUP_IDENTIFIER = 'state_list_by_group';
    private const STATE_ID_IDENTIFIER = 'state_identifier';
    private const STATE_ID_IDENTIFIER_WITH_BY_GROUP_SUFFIX_IDENTIFIER = 'state_identifier_with_by_group_suffix';
    private const BY_GROUP_IDENTIFIER = 'by_group';
    private const STATE_BY_GROUP_ON_CONTENT_IDENTIFIER = 'state_by_group_on_content';
    private const STATE_BY_GROUP_IDENTIFIER = 'state_by_group';
    private const CONTENT_IDENTIFIER = 'content';
    private const ON_CONTENT_IDENTIFIER = 'on_content';

    /**
     * {@inheritdoc}
     */
    public function createGroup(InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $input]);
        $group = $this->persistenceHandler->objectStateHandler()->createGroup($input);

        $this->cache->deleteItem(
            $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_ALL_IDENTIFIER, [], true)
        );

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        return $this->getCacheValue(
            (int) $groupId,
            $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_IDENTIFIER, [], true) . '-',
            function (int $groupId): Group {
                $this->logger->logCall(__METHOD__, ['groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadGroup($groupId);
            },
            function () use ($groupId): array {
                return [
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [(int) $groupId]),
                ];
            },
            function () use ($groupId) {
                return [
                    $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_IDENTIFIER, [(int) $groupId], true),
                ];
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
            $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_IDENTIFIER, [], true) . '-',
            function (string $identifier): Group {
                $this->logger->logCall(__METHOD__, ['groupId' => $identifier]);

                return $this->persistenceHandler->objectStateHandler()->loadGroupByIdentifier($identifier);
            },
            function (Group $group): array {
                return [
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [$group->id]),
                ];
            },
            function (Group $group) use ($escapedIdentifier): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_WITH_ID_SUFFIX_IDENTIFIER, [$escapedIdentifier], true),
                    $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_IDENTIFIER, [$group->id], true),
                ];
            },
            $this->cacheIdentifierGenerator->generateKey(self::BY_IDENTIFIER_SUFFIX)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups($offset = 0, $limit = -1)
    {
        $stateGroups = $this->getListCacheValue(
            $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_ALL_IDENTIFIER, [], true),
            function () use ($offset, $limit): array {
                $this->logger->logCall(__METHOD__, ['offset' => (int) $offset, 'limit' => (int) $limit]);

                return $this->persistenceHandler->objectStateHandler()->loadAllGroups(0, -1);
            },
            function (Group $group): array {
                return [
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [$group->id]),
                ];
            },
            function (Group $group): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_IDENTIFIER, [$group->id], true),
                    $this->cacheIdentifierGenerator->generateKey(self::STATE_GROUP_WITH_ID_SUFFIX_IDENTIFIER, [$group->id], true),
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
            $this->cacheIdentifierGenerator->generateKey(self::STATE_LIST_BY_GROUP_IDENTIFIER, [], true) . '-',
            function (int $groupId): array {
                $this->logger->logCall(__METHOD__, ['groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadObjectStates($groupId);
            },
            function (array $objectStates) use ($groupId): array {
                $cacheTags = [];
                $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [$groupId]);
                foreach ($objectStates as $state) {
                    $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::STATE_IDENTIFIER, [$state->id]);
                }

                return $cacheTags;
            },
            function () use ($groupId): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(self::STATE_LIST_BY_GROUP_IDENTIFIER, [$groupId], true),
                ];
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

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [$groupId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId]);
        $return = $this->persistenceHandler->objectStateHandler()->deleteGroup($groupId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [$groupId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function create($groupId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['groupId' => $groupId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->create($groupId, $input);

        $this->cache->deleteItem(
            $this->cacheIdentifierGenerator->generateKey(self::STATE_LIST_BY_GROUP_IDENTIFIER, [$groupId], true)
        );

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($stateId)
    {
        return $this->getCacheValue(
            (int) $stateId,
            $this->cacheIdentifierGenerator->generateKey(self::STATE_IDENTIFIER, [], true) . '-',
            function (int $stateId): ObjectState {
                $this->logger->logCall(__METHOD__, ['stateId' => $stateId]);

                return $this->persistenceHandler->objectStateHandler()->load($stateId);
            },
            function (ObjectState $objectState): array {
                return [
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_IDENTIFIER, [$objectState->id]),
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [$objectState->groupId]),
                ];
            },
            function () use ($stateId): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(self::STATE_IDENTIFIER, [$stateId], true),
                ];
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
            $this->cacheIdentifierGenerator->generateKey(self::STATE_ID_IDENTIFIER, [], true) . '-',
            function (string $identifier) use ($groupId): ObjectState {
                $this->logger->logCall(__METHOD__, ['identifier' => $identifier, 'groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadByIdentifier($identifier, (int) $groupId);
            },
            function (ObjectState $objectState): array {
                return [
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_IDENTIFIER, [$objectState->id]),
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_GROUP_IDENTIFIER, [$objectState->groupId]),
                ];
            },
            function () use ($escapedIdentifier, $groupId): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(
                        self::STATE_ID_IDENTIFIER_WITH_BY_GROUP_SUFFIX_IDENTIFIER,
                        [$escapedIdentifier, $groupId],
                        true
                    ),
                ];
            },
            '-' . $this->cacheIdentifierGenerator->generateKey(self::BY_GROUP_IDENTIFIER, [$groupId])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update($stateId, InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId, 'struct' => $input]);
        $return = $this->persistenceHandler->objectStateHandler()->update($stateId, $input);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::STATE_IDENTIFIER, [$stateId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($stateId, $priority)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId, 'priority' => $priority]);
        $return = $this->persistenceHandler->objectStateHandler()->setPriority($stateId, $priority);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::STATE_IDENTIFIER, [$stateId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($stateId)
    {
        $this->logger->logCall(__METHOD__, ['stateId' => $stateId]);
        $return = $this->persistenceHandler->objectStateHandler()->delete($stateId);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::STATE_IDENTIFIER, [$stateId]),
        ]);

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
            $this->cacheIdentifierGenerator->generateKey(
                self::STATE_BY_GROUP_ON_CONTENT_IDENTIFIER,
                [$groupId, $contentId],
                true
            )
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
            $this->cacheIdentifierGenerator->generateKey(self::STATE_BY_GROUP_IDENTIFIER, [], true) . '-',
            function (int $stateGroupId) use ($contentId): ObjectState {
                $this->logger->logCall(__METHOD__, ['contentId' => (int) $contentId, 'stateGroupId' => $stateGroupId]);

                return $this->persistenceHandler->objectStateHandler()->getContentState((int) $contentId, $stateGroupId);
            },
            function (ObjectState $contentState) use ($contentId): array {
                return [
                    $this->cacheIdentifierGenerator->generateTag(self::STATE_IDENTIFIER, [$contentState->id]),
                    $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
                ];
            },
            function () use ($contentId, $stateGroupId): array {
                return [
                    $this->cacheIdentifierGenerator->generateKey(
                        self::STATE_BY_GROUP_ON_CONTENT_IDENTIFIER,
                        [$stateGroupId, $contentId],
                        true
                    ),
                ];
            },
            '-' . $this->cacheIdentifierGenerator->generateKey(self::ON_CONTENT_IDENTIFIER, [$contentId])
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
