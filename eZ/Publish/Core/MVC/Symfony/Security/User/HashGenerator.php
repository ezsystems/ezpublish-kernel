<?php
/**
 * File containing the user HashGenerator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\SPI\HashGenerator as HashGeneratorInterface;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\SPI\User\Identity;

/**
 * User hash generator.
 *
 * @todo Allow several services to plug-in and add information to the user identity (via a dedicated service tag)
 */
class HashGenerator implements HashGeneratorInterface
{
    /**
     * @var \eZ\Publish\SPI\User\Identity
     */
    protected $userIdentity;

    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    public function __construct( Identity $userIdentity, Repository $repository )
    {
        $this->userIdentity = $userIdentity;
        $this->repository = $repository;
    }

    /**
     * Generates the user hash
     *
     * @return string
     */
    public function generate()
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

        $this->userIdentity->setInformation( 'roleIdList', implode( '.', $roleIds ) );

        return $this->userIdentity->getHash();
    }
}
