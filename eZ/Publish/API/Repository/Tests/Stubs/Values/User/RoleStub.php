<?php
/**
 * File containing the RoleStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\User;

use \eZ\Publish\API\Repository\Values\User\Role;
use \eZ\Publish\API\Repository\Values\User\Policy;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\User\Role}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\Role
 */
class RoleStub extends Role
{
    /**
     * returns the list of policies of this role
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Policy}
     */
    public function getPolicies()
    {
        // TODO: Implement getPolicies() method.
    }

    /**
     * returns the policy for the given module and function
     *
     * @param string $module
     * @param string $function
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function getPolicy( $module, $function )
    {
        // TODO: Implement getPolicy() method.
    }
}
