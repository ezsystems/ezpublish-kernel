<?php
/**
 * File containing the Role class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\User;
use ezp\Persistence\ValueObject;

/**
 */
class Role extends ValueObject
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    public $id;

    /**
     * Name of the role
     *
     * @var string
     */
    public $name;

    /**
     * Policies associated with the role
     *
     * @var Policy[]
     */
    public $policies = array();

    /**
     * Contains an array of group IDs that has this role assigned.
     *
     * @var mixed[] In current implementation, id's are contentId's
     */
    public $groupIds = array();
}
?>
