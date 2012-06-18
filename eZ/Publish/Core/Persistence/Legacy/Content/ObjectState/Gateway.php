<?php
/**
 * File containing the ObjectState Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState;

/**
 * ObjectState Gateway
 */
abstract class Gateway
{
    /**
     * Loads data for an object state
     *
     * @param mixed $stateId
     * @return array
     */
    abstract public function loadObjectStateData( $stateId );

    /**
     * Loads data for all object states belonging to group with $groupId ID
     *
     * @param mixed $groupId
     * @return array
     */
    abstract public function loadObjectStateListData( $groupId );

    /**
     * Loads data for an object state group
     *
     * @param mixed $groupId
     * @return array
     */
    abstract public function loadObjectStateGroupData( $groupId );

    /**
     * Loads data for all object state groups, filtered by $offset and $limit
     *
     * @param int $offset
     * @param int $limit
     * @return array
     */
    abstract public function loadObjectStateGroupListData( $offset, $limit );
}
