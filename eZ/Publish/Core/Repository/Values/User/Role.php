<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\User\Role class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;

/**
 * This class represents a role
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\Policy[] $policies Policies assigned to this role
 */
class Role extends APIRole
{
    /**
     * Policies assigned to this role
     *
     * @var \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    protected $policies = array();

    /**
     * returns the list of policies of this role
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function getPolicies()
    {
        return $this->policies;
    }
}
