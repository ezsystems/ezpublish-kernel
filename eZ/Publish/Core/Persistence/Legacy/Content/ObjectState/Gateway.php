<?php

/**
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
     * Load data for an object state.
     */
    abstract public function loadObjectStateData(int $stateId): array;

    /**
     * Load data for an object state by identifier.
     */
    abstract public function loadObjectStateDataByIdentifier(
        string $identifier,
        int $groupId
    ): array;

    /**
     * Load data for all object states belonging to group with $groupId ID.
     */
    abstract public function loadObjectStateListData(int $groupId): array;

    /**
     * Load data for an object state group.
     */
    abstract public function loadObjectStateGroupData(int $groupId): array;

    /**
     * Load data for an object state group by identifier.
     */
    abstract public function loadObjectStateGroupDataByIdentifier(string $identifier): array;

    /**
     * Load data for all object state groups, filtered by $offset and $limit.
     */
    abstract public function loadObjectStateGroupListData(int $offset, int $limit): array;

    /**
     * Insert a new object state into database.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    abstract public function insertObjectState(ObjectState $objectState, int $groupId): void;

    /**
     * Update the stored object state with provided data.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    abstract public function updateObjectState(ObjectState $objectState): void;

    /**
     * Delete object state identified by $stateId.
     */
    abstract public function deleteObjectState(int $stateId): void;

    /**
     * Update object state links from $oldStateId to $newStateId.
     */
    abstract public function updateObjectStateLinks(int $oldStateId, int $newStateId): void;

    /**
     * Delete object state links identified by $stateId.
     */
    abstract public function deleteObjectStateLinks(int $stateId): void;

    /**
     * Insert a new object state group into database.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if Object State Group language does not exist
     */
    abstract public function insertObjectStateGroup(Group $objectStateGroup): void;

    /**
     * Update the stored object state group with provided data.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    abstract public function updateObjectStateGroup(Group $objectStateGroup): void;

    /**
     * Delete the object state group identified by $groupId.
     */
    abstract public function deleteObjectStateGroup(int $groupId): void;

    /**
     * Set the object state $stateId to content with $contentId ID.
     */
    abstract public function setContentState(int $contentId, int $groupId, int $stateId): void;

    /**
     * Load object state data for $contentId content from $stateGroupId state group.
     */
    abstract public function loadObjectStateDataForContent(
        int $contentId,
        int $stateGroupId
    ): array;

    /**
     * Return the number of objects which are in this state.
     */
    abstract public function getContentCount(int $stateId): int;

    /**
     * Update the object state priority to provided value.
     */
    abstract public function updateObjectStatePriority(int $stateId, int $priority): void;
}
