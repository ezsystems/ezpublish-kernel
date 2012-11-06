<?php
/**
 * File containing the Role class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\Role}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\Role
 */
class Role extends \eZ\Publish\API\Repository\Values\User\Role
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    protected $policies;

    /**
     * Instantiates a role stub instance.
     *
     * @param array $properties
     * @param \eZ\Publish\API\Repository\Values\User\Policy[] $policies
     */
    public function __construct( array $properties = array(), array $policies = array() )
    {
        parent::__construct( $properties );

        $this->policies = $policies;
    }

    /**
     * returns the list of policies of this role
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function getPolicies()
    {
        return $this->policies;
    }
}
