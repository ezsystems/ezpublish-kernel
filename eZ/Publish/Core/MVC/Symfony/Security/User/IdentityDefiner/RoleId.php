<?php
/**
 * File containing the RoleId identify definer class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\User\IdentityDefiner;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\SPI\User\Identity;
use eZ\Publish\SPI\User\IdentityAware;

/**
 * Identity definer based on current user role ids.
 */
class RoleId implements IdentityAware
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    public function __construct( Repository $repository )
    {
        $this->repository = $repository;
    }

    public function setIdentity( Identity $identity )
    {
        $user = $this->repository->getCurrentUser();
        $roles = $this->repository->sudo(
            function ( $repository ) use ( $user )
            {
                return $repository->getRoleService()->getRoleAssignmentsForUser( $user, true );
            }
        );
        $roleIds = array();
        foreach ( $roles as $roleAssignment )
        {
            $roleIds[] = $roleAssignment->role->id;
        }

        $identity->setInformation( 'roleIdList', implode( '.', $roleIds ) );
    }
}
