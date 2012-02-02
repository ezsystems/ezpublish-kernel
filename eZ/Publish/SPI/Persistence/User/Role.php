<?php
/**
 * File containing the Role class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\ValueObject;

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
     * In legacy storage engine, these are the contentobject_id's in ezuser_role.
     * These id's can by the nature of legacy engine both point to a user group and a user,
     * but the latter is deprecated.
     *
     * @var mixed[] In current implementation, id's are contentId's
     */
    public $groupIds = array();
}
?>
