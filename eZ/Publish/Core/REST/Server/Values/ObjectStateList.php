<?php
/**
 * File containing the ObjectStateList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

/**
 * ObjectState list view model
 */
class ObjectStateList
{
    /**
     * Object states
     *
     * @var array
     */
    public $states;

    /**
     * ID of the group that the states belong to
     *
     * @var int
     */
    public $groupId;

    /**
     * Construct
     *
     * @param array $states
     * @param int $groupId
     */
    public function __construct( array $states, $groupId )
    {
        $this->states = $states;
        $this->groupId = $groupId;
    }
}
