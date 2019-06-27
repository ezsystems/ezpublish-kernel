<?php

/**
 * File containing the RoleDomainMapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\Core\Repository\Values\User\PolicyDraft;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\Core\Repository\Values\User\RoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Persistence\User\Policy as SPIPolicy;
use eZ\Publish\SPI\Persistence\User\RoleAssignment as SPIRoleAssignment;
use eZ\Publish\SPI\Persistence\User\Role as SPIRole;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct as SPIRoleCreateStruct;

/**
 * Internal service to map Role objects between API and SPI values.
 *
 * @internal Meant for internal use by Repository.
 */
class RoleDomainMapper
{
    /** @var \eZ\Publish\Core\Repository\Helper\LimitationService */
    protected $limitationService;

    /**
     * @param \eZ\Publish\Core\Repository\Helper\LimitationService $limitationService
     */
    public function __construct(LimitationService $limitationService)
    {
        $this->limitationService = $limitationService;
    }

    /**
     * Maps provided SPI Role value object to API Role value object.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function buildDomainRoleObject(SPIRole $role)
    {
        $rolePolicies = [];
        foreach ($role->policies as $spiPolicy) {
            $rolePolicies[] = $this->buildDomainPolicyObject($spiPolicy);
        }

        return new Role(
            [
                'id' => $role->id,
                'identifier' => $role->identifier,
                'status' => $role->status,
                'policies' => $rolePolicies,
            ]
        );
    }

    /**
     * Builds a RoleDraft domain object from value object returned by persistence
     * Decorates Role.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $spiRole
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function buildDomainRoleDraftObject(SPIRole $spiRole)
    {
        return new RoleDraft(
            [
                'innerRole' => $this->buildDomainRoleObject($spiRole),
            ]
        );
    }

    /**
     * Maps provided SPI Policy value object to API Policy value object.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $spiPolicy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy|\eZ\Publish\API\Repository\Values\User\PolicyDraft
     */
    public function buildDomainPolicyObject(SPIPolicy $spiPolicy)
    {
        $policyLimitations = [];
        if ($spiPolicy->module !== '*' && $spiPolicy->function !== '*' && $spiPolicy->limitations !== '*') {
            foreach ($spiPolicy->limitations as $identifier => $values) {
                $policyLimitations[] = $this->limitationService->getLimitationType($identifier)->buildValue($values);
            }
        }

        $policy = new Policy(
            [
                'id' => $spiPolicy->id,
                'roleId' => $spiPolicy->roleId,
                'module' => $spiPolicy->module,
                'function' => $spiPolicy->function,
                'limitations' => $policyLimitations,
            ]
        );

        // Original ID is set on SPI policy, which means that it's a draft.
        if ($spiPolicy->originalId) {
            $policy = new PolicyDraft(['innerPolicy' => $policy, 'originalId' => $spiPolicy->originalId]);
        }

        return $policy;
    }

    /**
     * Builds the API UserRoleAssignment object from provided SPI RoleAssignment object.
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleAssignment $spiRoleAssignment
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment
     */
    public function buildDomainUserRoleAssignmentObject(SPIRoleAssignment $spiRoleAssignment, User $user, APIRole $role)
    {
        $limitation = null;
        if (!empty($spiRoleAssignment->limitationIdentifier)) {
            $limitation = $this
                ->limitationService
                ->getLimitationType($spiRoleAssignment->limitationIdentifier)
                ->buildValue($spiRoleAssignment->values);
        }

        return new UserRoleAssignment(
            [
                'id' => $spiRoleAssignment->id,
                'limitation' => $limitation,
                'role' => $role,
                'user' => $user,
            ]
        );
    }

    /**
     * Builds the API UserGroupRoleAssignment object from provided SPI RoleAssignment object.
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleAssignment $spiRoleAssignment
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment
     */
    public function buildDomainUserGroupRoleAssignmentObject(SPIRoleAssignment $spiRoleAssignment, UserGroup $userGroup, APIRole $role)
    {
        $limitation = null;
        if (!empty($spiRoleAssignment->limitationIdentifier)) {
            $limitation = $this
                ->limitationService
                ->getLimitationType($spiRoleAssignment->limitationIdentifier)
                ->buildValue($spiRoleAssignment->values);
        }

        return new UserGroupRoleAssignment(
            [
                'id' => $spiRoleAssignment->id,
                'limitation' => $limitation,
                'role' => $role,
                'userGroup' => $userGroup,
            ]
        );
    }

    /**
     * Creates SPI Role create struct from provided API role create struct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleCreateStruct
     */
    public function buildPersistenceRoleCreateStruct(APIRoleCreateStruct $roleCreateStruct)
    {
        $policiesToCreate = [];
        foreach ($roleCreateStruct->getPolicies() as $policyCreateStruct) {
            $policiesToCreate[] = $this->buildPersistencePolicyObject(
                $policyCreateStruct->module,
                $policyCreateStruct->function,
                $policyCreateStruct->getLimitations()
            );
        }

        return new SPIRoleCreateStruct(
            [
                'identifier' => $roleCreateStruct->identifier,
                'policies' => $policiesToCreate,
            ]
        );
    }

    /**
     * Creates SPI Policy value object from provided module, function and limitations.
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function buildPersistencePolicyObject($module, $function, array $limitations)
    {
        $limitationsToCreate = '*';
        if ($module !== '*' && $function !== '*' && !empty($limitations)) {
            $limitationsToCreate = [];
            foreach ($limitations as $limitation) {
                $limitationsToCreate[$limitation->getIdentifier()] = $limitation->limitationValues;
            }
        }

        return new SPIPolicy(
            [
                'module' => $module,
                'function' => $function,
                'limitations' => $limitationsToCreate,
            ]
        );
    }
}
