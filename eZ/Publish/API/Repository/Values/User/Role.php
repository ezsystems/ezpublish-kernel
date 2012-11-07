<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Role class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a role
 *
 * @property-read mixed $id the internal id of the role
 * @property-read string $identifier the identifier of the role
 * @property-read array $policies an array of the policies {@link \eZ\Publish\API\Repository\Values\User\Policy} of the role.
 */
abstract class Role extends ValueObject
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    protected $id;

     /**
     * Readable string identifier of a role
     * in 4.x. this is mapped to the role name
     *
     * @var string
     */
    protected $identifier;

    /**
     * returns the list of policies of this role
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    abstract public function getPolicies();
}
