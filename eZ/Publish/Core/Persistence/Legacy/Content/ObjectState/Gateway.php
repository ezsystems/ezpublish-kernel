<?php

/**
 * File containing the ObjectState Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState;

use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;

/**
 * Base class for Object State gateways.
 *
 * @internal For internal use by Persistence Handlers.
 */
abstract class Gateway
{
    public const OBJECT_STATE_TABLE = 'ezcobj_state';
    public const OBJECT_STATE_LANGUAGE_TABLE = 'ezcobj_state_language';
    public const OBJECT_STATE_GROUP_TABLE = 'ezcobj_state_group';
    public const OBJECT_STATE_GROUP_LANGUAGE_TABLE = 'ezcobj_state_group_language';
    public const OBJECT_STATE_LINK_TABLE = 'ezcobj_state_link';

    public const OBJECT_STATE_TABLE_SEQ = 'ezcobj_state_id_seq';
    public const OBJECT_STATE_GROUP_TABLE_SEQ = 'ezcobj_state_group_id_seq';

    /**
     * Loads data for an object state.
     *
     * @param mixed $stateId
     *
     * @return array
     */
    abstract public function loadObjectStateData(int $stateId): array;

    /**
     * Loads data for an object state by identifier.
     *
     * @param string $identifier
     * @param mixed $groupId
     *
     * @return array
     */
    abstract public function loadObjectStateDataByIdentifier(
        string $identifier,
        int $groupId
    ): array;

    /**
     * Loads data for all object states belonging to group with $groupId ID.
     *
     * @param mixed $groupId
     *
     * @return array
     */
    abstract public function loadObjectStateListData(int $groupId): array;

    /**
     * Loads data for an object state group.
     *
     * @param mixed $groupId
     *
     * @return array
     */
    abstract public function loadObjectStateGroupData(int $groupId): array;

    /**
     * Loads data for an object state group by identifier.
     *
     * @param string $identifier
     *
     * @return array
     */
    abstract public function loadObjectStateGroupDataByIdentifier(string $identifier): array;

    /**
     * Loads data for all object state groups, filtered by $offset and $limit.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    abstract public function loadObjectStateGroupListData(int $offset, int $limit): array;

    /**
     * Inserts a new object state into database.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     * @param int $groupId
     */
    abstract public function insertObjectState(ObjectState $objectState, int $groupId): void;

    /**
     * Updates the stored object state with provided data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     */
    abstract public function updateObjectState(ObjectState $objectState): void;

    /**
     * Deletes object state identified by $stateId.
     *
     * @param int $stateId
     */
    abstract public function deleteObjectState(int $stateId): void;

    /**
     * Update object state links from $oldStateId to $newStateId.
     *
     * @param int $oldStateId
     * @param int $newStateId
     */
    abstract public function updateObjectStateLinks(int $oldStateId, int $newStateId): void;

    /**
     * Deletes object state links identified by $stateId.
     *
     * @param int $stateId
     */
    abstract public function deleteObjectStateLinks(int $stateId): void;

    /**
     * Inserts a new object state group into database.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    abstract public function insertObjectStateGroup(Group $objectStateGroup): void;

    /**
     * Updates the stored object state group with provided data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    abstract public function updateObjectStateGroup(Group $objectStateGroup): void;

    /**
     * Deletes the object state group identified by $groupId.
     *
     * @param mixed $groupId
     */
    abstract public function deleteObjectStateGroup(int $groupId): void;

    /**
     * Sets the object state $stateId to content with $contentId ID.
     *
     * @param mixed $contentId
     * @param mixed $groupId
     * @param mixed $stateId
     */
    abstract public function setContentState(int $contentId, int $groupId, int $stateId): void;

    /**
     * Loads object state data for $contentId content from $stateGroupId state group.
     *
     * @param int $contentId
     * @param int $stateGroupId
     *
     * @return array
     */
    abstract public function loadObjectStateDataForContent(
        int $contentId,
        int $stateGroupId
    ): array;

    /**
     * Returns the number of objects which are in this state.
     *
     * @param mixed $stateId
     *
     * @return int
     */
    abstract public function getContentCount(int $stateId): int;

    /**
     * Updates the object state priority to provided value.
     *
     * @param mixed $stateId
     * @param int $priority
     */
    abstract public function updateObjectStatePriority(int $stateId, int $priority): void;
}
