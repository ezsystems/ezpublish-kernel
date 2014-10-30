<?php
/**
 * File containing the RoleDomainMapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Persistence\User\Policy as SPIPolicy;
use eZ\Publish\SPI\Persistence\User\RoleAssignment as SPIRoleAssignment;
use eZ\Publish\SPI\Persistence\User\Role as SPIRole;

/**
 * Internal service to map Role objects between API and SPI values
 *
 * @package eZ\Publish\Core\Repository
 */
class RoleDomainMapper
{
    /**
     * @var \eZ\Publish\Core\Repository\Helper\LimitationService
     */
    protected $limitationService;

    /**
     * @param \eZ\Publish\Core\Repository\Helper\LimitationService $limitationService
     */
    public function __construct( LimitationService $limitationService )
    {
        $this->limitationService = $limitationService;
    }

    /**
     * Maps provided SPI Role value object to API Role value object
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function buildDomainRoleObject( SPIRole $role )
    {
        $rolePolicies = array();
        foreach ( $role->policies as $spiPolicy )
        {
            $rolePolicies[] = $this->buildDomainPolicyObject( $spiPolicy );
        }

        return new Role(
            array(
                'id' => $role->id,
                'identifier' => $role->identifier,
                'policies' => $rolePolicies
            )
        );
    }

    /**
     * Maps provided SPI Policy value object to API Policy value object
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function buildDomainPolicyObject( SPIPolicy $policy )
    {
        $policyLimitations = array();
        if ( $policy->module !== '*' && $policy->function !== '*' && $policy->limitations !== '*' )
        {
            foreach ( $policy->limitations as $identifier => $values )
            {
                $policyLimitations[] = $this->limitationService->getLimitationType( $identifier )->buildValue( $values );
            }
        }

        if ( $policy->roleId === null )
            throw new \Exception( "null" );

        return new Policy(
            array(
                'id' => $policy->id,
                'roleId' => $policy->roleId,
                'module' => $policy->module,
                'function' => $policy->function,
                'limitations' => $policyLimitations
            )
        );
    }

    /**
     * Builds the API UserRoleAssignment object from provided SPI RoleAssignment object
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleAssignment $spiRoleAssignment
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment
     */
    public function buildDomainUserRoleAssignmentObject( SPIRoleAssignment $spiRoleAssignment, User $user, APIRole $role )
    {
        $limitation = null;
        if ( !empty( $spiRoleAssignment->limitationIdentifier ) )
        {
            $limitation = $this
                ->limitationService
                ->getLimitationType( $spiRoleAssignment->limitationIdentifier )
                ->buildValue( $spiRoleAssignment->values );
        }

        return new UserRoleAssignment(
            array(
                'limitation' => $limitation,
                'role' => $role,
                'user' => $user
            )
        );
    }

    /**
     * Builds the API UserGroupRoleAssignment object from provided SPI RoleAssignment object
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleAssignment $spiRoleAssignment
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment
     */
    public function buildDomainUserGroupRoleAssignmentObject( SPIRoleAssignment $spiRoleAssignment, UserGroup $userGroup, APIRole $role )
    {
        $limitation = null;
        if ( !empty( $spiRoleAssignment->limitationIdentifier ) )
        {
            $limitation = $this
                ->limitationService
                ->getLimitationType( $spiRoleAssignment->limitationIdentifier )
                ->buildValue( $spiRoleAssignment->values );
        }

        return new UserGroupRoleAssignment(
            array(
                'limitation' => $limitation,
                'role' => $role,
                'userGroup' => $userGroup
            )
        );
    }

    /**
     * Creates SPI Role value object from provided API role create struct
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function buildPersistenceRoleObject( APIRoleCreateStruct $roleCreateStruct )
    {
        $policiesToCreate = array();
        foreach ( $roleCreateStruct->getPolicies() as $policyCreateStruct )
        {
            $policiesToCreate[] = $this->buildPersistencePolicyObject(
                $policyCreateStruct->module,
                $policyCreateStruct->function,
                $policyCreateStruct->getLimitations()
            );
        }

        return new SPIRole(
            array(
                'identifier' => $roleCreateStruct->identifier,
                'policies' => $policiesToCreate
            )
        );
    }

    /**
     * Creates SPI Policy value object from provided module, function and limitations
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function buildPersistencePolicyObject( $module, $function, array $limitations )
    {
        $limitationsToCreate = "*";
        if ( $module !== '*' && $function !== '*' && !empty( $limitations ) )
        {
            $limitationsToCreate = array();
            foreach ( $limitations as $limitation )
            {
                $limitationsToCreate[$limitation->getIdentifier()] = $limitation->limitationValues;
            }
        }

        return new SPIPolicy(
            array(
                'module' => $module,
                'function' => $function,
                'limitations' => $limitationsToCreate
            )
        );
    }
}
