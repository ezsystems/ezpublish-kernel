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
    private const STATE_GROUP_ALL_TAG = 'state_group_all';
    private const STATE_GROUP_TAG = 'state_group';
    private const STATE_GROUP_WITH_ID_SUFFIX_TAG = 'state_group_with_id_suffix';
    private const BY_IDENTIFIER_SUFFIX = 'by_identifier_suffix';
    private const STATE_TAG = 'state';
    private const STATE_LIST_BY_GROUP_TAG = 'state_list_by_group';
    private const STATE_IDENTIFIER = 'state_identifier';
    private const STATE_IDENTIFIER_WITH_BY_GROUP_SUFFIX_TAG = 'state_identifier_with_by_group_suffix';
    private const BY_GROUP_TAG = 'by_group';
    private const STATE_BY_GROUP_ON_CONTENT_TAG = 'state_by_group_on_content';
    private const STATE_BY_GROUP_TAG = 'state_by_group';
    private const CONTENT_TAG = 'content';
    private const ON_CONTENT_TAG = 'on_content';

    /**
     * {@inheritdoc}
     */
    public function createGroup(InputStruct $input)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $input]);
        $group = $this->persistenceHandler->objectStateHandler()->createGroup($input);

        $this->cache->deleteItem(
            $this->tagGenerator->generate(self::STATE_GROUP_ALL_TAG, [], true)
        );

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function loadGroup($groupId)
    {
        $tagGenerator = $this->tagGenerator;

        return $this->getCacheValue(
            (int) $groupId,
            $tagGenerator->generate(self::STATE_GROUP_TAG, [], true) . '-',
            function (int $groupId): Group {
                $this->logger->logCall(__METHOD__, ['groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadGroup($groupId);
            },
            static function () use ($groupId, $tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [(int) $groupId]),
                ];
            },
            static function () use ($groupId, $tagGenerator) {
                return [
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [(int) $groupId], true),
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
        $tagGenerator = $this->tagGenerator;

        return $this->getCacheValue(
            $identifier,
            $tagGenerator->generate(self::STATE_GROUP_TAG, [], true) . '-',
            function (string $identifier): Group {
                $this->logger->logCall(__METHOD__, ['groupId' => $identifier]);

                return $this->persistenceHandler->objectStateHandler()->loadGroupByIdentifier($identifier);
            },
            static function (Group $group) use ($tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [$group->id]),
                ];
            },
            static function (Group $group) use ($escapedIdentifier, $tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_GROUP_WITH_ID_SUFFIX_TAG, [$escapedIdentifier], true),
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [$group->id], true),
                ];
            },
            $tagGenerator->generate(self::BY_IDENTIFIER_SUFFIX)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllGroups($offset = 0, $limit = -1)
    {
        $tagGenerator = $this->tagGenerator;

        $stateGroups = $this->getListCacheValue(
            $tagGenerator->generate(self::STATE_GROUP_ALL_TAG, [], true),
            function () use ($offset, $limit): array {
                $this->logger->logCall(__METHOD__, ['offset' => (int) $offset, 'limit' => (int) $limit]);

                return $this->persistenceHandler->objectStateHandler()->loadAllGroups(0, -1);
            },
            static function (Group $group) use ($tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [$group->id]),
                ];
            },
            static function (Group $group) use ($tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [$group->id], true),
                    $tagGenerator->generate(self::STATE_GROUP_WITH_ID_SUFFIX_TAG, [$group->id], true),
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
        $tagGenerator = $this->tagGenerator;

        return $this->getCacheValue(
            $groupId,
            $tagGenerator->generate(self::STATE_LIST_BY_GROUP_TAG, [], true) . '-',
            function (int $groupId): array {
                $this->logger->logCall(__METHOD__, ['groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadObjectStates($groupId);
            },
            static function (array $objectStates) use ($groupId, $tagGenerator): array {
                $cacheTags = [];
                $cacheTags[] = $tagGenerator->generate(self::STATE_GROUP_TAG, [$groupId]);
                foreach ($objectStates as $state) {
                    $cacheTags[] = $tagGenerator->generate(self::STATE_TAG, [$state->id]);
                }

                return $cacheTags;
            },
            static function () use ($groupId, $tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_LIST_BY_GROUP_TAG, [$groupId], true),
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
            $this->tagGenerator->generate(self::STATE_GROUP_TAG, [$groupId]),
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
            $this->tagGenerator->generate(self::STATE_GROUP_TAG, [$groupId]),
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
            $this->tagGenerator->generate(self::STATE_LIST_BY_GROUP_TAG, [$groupId], true)
        );

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($stateId)
    {
        $tagGenerator = $this->tagGenerator;

        return $this->getCacheValue(
            (int) $stateId,
            $tagGenerator->generate(self::STATE_TAG, [], true) . '-',
            function (int $stateId): ObjectState {
                $this->logger->logCall(__METHOD__, ['stateId' => $stateId]);

                return $this->persistenceHandler->objectStateHandler()->load($stateId);
            },
            static function (ObjectState $objectState) use ($tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_TAG, [$objectState->id]),
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [$objectState->groupId]),
                ];
            },
            static function () use ($stateId, $tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_TAG, [$stateId], true),
                ];
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByIdentifier($identifier, $groupId)
    {
        $tagGenerator = $this->tagGenerator;
        $escapedIdentifier = $this->escapeForCacheKey($identifier);

        return $this->getCacheValue(
            $identifier,
            $tagGenerator->generate(self::STATE_IDENTIFIER, [], true) . '-',
            function (string $identifier) use ($groupId): ObjectState {
                $this->logger->logCall(__METHOD__, ['identifier' => $identifier, 'groupId' => (int) $groupId]);

                return $this->persistenceHandler->objectStateHandler()->loadByIdentifier($identifier, (int) $groupId);
            },
            static function (ObjectState $objectState) use ($tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_TAG, [$objectState->id]),
                    $tagGenerator->generate(self::STATE_GROUP_TAG, [$objectState->groupId]),
                ];
            },
            static function () use ($escapedIdentifier, $groupId, $tagGenerator): array {
                return [
                    $tagGenerator->generate(
                        self::STATE_IDENTIFIER_WITH_BY_GROUP_SUFFIX_TAG,
                        [$escapedIdentifier, $groupId],
                        true
                    ),
                ];
            },
            '-' . $tagGenerator->generate(self::BY_GROUP_TAG, [$groupId])
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
            $this->tagGenerator->generate(self::STATE_TAG, [$stateId]),
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
            $this->tagGenerator->generate(self::STATE_TAG, [$stateId]),
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
            $this->tagGenerator->generate(self::STATE_TAG, [$stateId]),
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
            $this->tagGenerator->generate(
                self::STATE_BY_GROUP_ON_CONTENT_TAG,
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
        $tagGenerator = $this->tagGenerator;

        return $this->getCacheValue(
            (int) $stateGroupId,
            $tagGenerator->generate(self::STATE_BY_GROUP_TAG, [], true) . '-',
            function (int $stateGroupId) use ($contentId): ObjectState {
                $this->logger->logCall(__METHOD__, ['contentId' => (int) $contentId, 'stateGroupId' => $stateGroupId]);

                return $this->persistenceHandler->objectStateHandler()->getContentState((int) $contentId, $stateGroupId);
            },
            static function (ObjectState $contentState) use ($contentId, $tagGenerator): array {
                return [
                    $tagGenerator->generate(self::STATE_TAG, [$contentState->id]),
                    $tagGenerator->generate(self::CONTENT_TAG, [$contentId]),
                ];
            },
            static function () use ($contentId, $stateGroupId, $tagGenerator): array {
                return [
                    $tagGenerator->generate(
                        self::STATE_BY_GROUP_ON_CONTENT_TAG,
                        [$stateGroupId, $contentId],
                        true
                    ),
                ];
            },
            '-' . $tagGenerator->generate(self::ON_CONTENT_TAG, [$contentId])
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
