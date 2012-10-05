<?php
/**
 * File containing the RestUserRoleAssignment class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestUserRoleAssignment view model
 */
class RestUserRoleAssignment extends RestValue
{
    /**
     * Role assignment
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserRoleAssignment
     */
    public $roleAssignment;

    /**
     * User ID to which the role is assigned
     *
     * @var mixed
     */
    public $id;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserRoleAssignment $roleAssignment
     * @param mixed $id
     */
    public function __construct( UserRoleAssignment $roleAssignment, $id )
    {
        $this->roleAssignment = $roleAssignment;
        $this->id = $id;
    }
}
