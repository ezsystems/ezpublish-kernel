<?php
/**
 * File containing the ObjectStateList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * ObjectState list view model
 */
class ObjectStateList extends RestValue
{
    /**
     * Object states
     *
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public $states;

    /**
     * ID of the group that the states belong to
     *
     * @var mixed
     */
    public $groupId;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[] $states
     * @param mixed $groupId
     */
    public function __construct( array $states, $groupId )
    {
        $this->states = $states;
        $this->groupId = $groupId;
    }
}
