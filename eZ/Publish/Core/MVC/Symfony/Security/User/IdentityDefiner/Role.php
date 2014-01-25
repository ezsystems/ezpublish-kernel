<?php
/**
 * File containing the RoleId identify definer class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\User\IdentityDefiner;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\UserRoleAssignment;
use eZ\Publish\SPI\User\Identity;
use eZ\Publish\SPI\User\IdentityAware;

/**
 * Identity definer based on current user role ids and role limitations.
 */
class Role implements IdentityAware
{
    /**
     * @var \eZ\Publish\Core\Repository\DomainLogic\Repository
     */
    protected $repository;

    public function __construct( Repository $repository )
    {
        $this->repository = $repository;
    }

    public function setIdentity( Identity $identity )
    {
        $user = $this->repository->getCurrentUser();
        $roleAssignments = $this->repository->sudo(
            function ( $repository ) use ( $user )
            {
                return $repository->getRoleService()->getRoleAssignmentsForUser( $user, true );
            }
        );

        $roleIds = array();
        $limitationValues = array();
        /** @var UserRoleAssignment $roleAssignment */
        foreach ( $roleAssignments as $roleAssignment )
        {
            $roleIds[] = $roleAssignment->role->id;
            // If a limitation is present, store the limitation values by roleId
            if ( $roleAssignment->limitation !== null )
            {
                $limitationValuesKey = "{$roleAssignment->role->id}-" . $roleAssignment->limitation->getIdentifier();
                $limitationValues[$limitationValuesKey] = array();
                foreach ( $roleAssignment->limitation->limitationValues as $value )
                {
                    $limitationValues[$limitationValuesKey][] = $value;
                }
            }
        }

        $identity->setInformation( 'roleIdList', implode( '|', $roleIds ) );

        // Flatten each limitation values to a string and then store it as Identity information
        $limitationValuesFlattened = array();
        foreach ( $limitationValues as $roleId => $limitationArray )
        {
            $limitationValuesFlattened[] = "$roleId:" . implode( '|', $limitationArray );
        }
        $identity->setInformation( 'roleLimitationList', implode( ',', $limitationValuesFlattened ) );
    }
}
