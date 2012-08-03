<?php
/**
 * File containing the ObjectState class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectState as APIObjectState;

/**
 * This class wraps the object state with added groupId property
 */
class ObjectState
{
    /**
     * Wrapped object state
     *
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public $objectState;

    /**
     * Group ID to which wrapped state belongs
     *
     * @var int
     */
    public $groupId;

    /**
     * Constructor
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param int $groupId
     */
    public function __construct( APIObjectState $objectState, $groupId )
    {
        $this->objectState = $objectState;
        $this->groupId = $groupId;
    }
}
