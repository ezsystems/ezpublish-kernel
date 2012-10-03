<?php
/**
 * File containing the ObjectStateGroupList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * ObjectStateGroup list view model
 */
class ObjectStateGroupList extends RestValue
{
    /**
     * Object state groups
     *
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public $groups;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[] $groups
     */
    public function __construct( array $groups )
    {
        $this->groups = $groups;
    }
}
