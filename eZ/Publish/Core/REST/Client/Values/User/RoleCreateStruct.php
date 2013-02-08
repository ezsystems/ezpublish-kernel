<?php
/**
 * File containing the RoleCreateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\RoleCreateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
 */
class RoleCreateStruct extends \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    private $policies = array();

    /**
     * Instantiates a role create class
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct( array( 'identifier' => $name ) );
    }

    /**
     * Returns policies associated with the role
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    public function getPolicies()
    {
        return $this->policies;
    }

    /**
     * Adds a policy to this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     */
    public function addPolicy( Values\User\PolicyCreateStruct $policyCreateStruct )
    {
        $this->policies[] = $policyCreateStruct;
    }
}
