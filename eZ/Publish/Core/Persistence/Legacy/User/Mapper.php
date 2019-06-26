<?php

/**
 * File containing the User mapper.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;

/**
 * mapper for User related objects.
 */
class Mapper
{
    /**
     * Map user data into user object.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function mapUser(array $data)
    {
        $user = new User();
        $user->id = $data['contentobject_id'];
        $user->login = $data['login'];
        $user->email = $data['email'];
        $user->passwordHash = $data['password_hash'];
        $user->hashAlgorithm = (int)$data['password_hash_type'];
        $user->isEnabled = (bool)$data['is_enabled'];
        $user->maxLogin = $data['max_login'];

        return $user;
    }

    /**
     * Map data for a set of user data.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function mapUsers(array $data)
    {
        $users = [];
        foreach ($data as $row) {
            $users[] = $this->mapUser($row);
        }

        return $users;
    }

    /**
     * Map policy data to an array of policies.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function mapPolicies(array $data)
    {
        /** @var \eZ\Publish\SPI\Persistence\User\Policy[] */
        $policies = [];
        foreach ($data as $row) {
            $policyId = $row['ezpolicy_id'];
            if (!isset($policies[$policyId]) && ($policyId !== null)) {
                $originalId = null;
                if ($row['ezpolicy_original_id']) {
                    $originalId = (int)$row['ezpolicy_original_id'];
                } elseif ($row['ezrole_version']) {
                    $originalId = (int)$policyId;
                }

                $policies[$policyId] = new Policy(
                    [
                        'id' => (int)$policyId,
                        'roleId' => (int)$row['ezrole_id'],
                        'originalId' => $originalId,
                        'module' => $row['ezpolicy_module_name'],
                        'function' => $row['ezpolicy_function_name'],
                        'limitations' => '*', // limitations must be '*' if not a non empty array of limitations
                    ]
                );
            }

            if (!$row['ezpolicy_limitation_identifier']) {
                continue;
            } elseif ($policies[$policyId]->limitations === '*') {
                $policies[$policyId]->limitations = [];
            }

            if (!isset($policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']])) {
                $policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']] = [$row['ezpolicy_limitation_value_value']];
            } elseif (!in_array($row['ezpolicy_limitation_value_value'], $policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']])) {
                $policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']][] = $row['ezpolicy_limitation_value_value'];
            }
        }

        return array_values($policies);
    }

    /**
     * Map role data to a role.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function mapRole(array $data)
    {
        $role = new Role();

        foreach ($data as $row) {
            if (empty($role->id)) {
                $role->id = (int)$row['ezrole_id'];
                $role->identifier = $row['ezrole_name'];
                $role->status = $row['ezrole_version'] != 0 ? Role::STATUS_DRAFT : Role::STATUS_DEFINED;
                $role->originalId = $row['ezrole_version'] ? (int)$row['ezrole_version'] : Role::STATUS_DEFINED;
                // skip name and description as they don't exist in legacy
            }
        }

        $role->policies = $this->mapPolicies($data);

        return $role;
    }

    /**
     * Map data for a set of roles.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function mapRoles(array $data)
    {
        $roleData = [];
        foreach ($data as $row) {
            $roleData[$row['ezrole_id']][] = $row;
        }

        $roles = [];
        foreach ($roleData as $data) {
            $roles[] = $this->mapRole($data);
        }

        return $roles;
    }

    /**
     * Map data for a set of role assignments.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function mapRoleAssignments(array $data)
    {
        $roleAssignmentData = [];
        foreach ($data as $row) {
            $id = (int)$row['id'];
            $roleId = (int)$row['role_id'];
            $contentId = (int)$row['contentobject_id'];
            // if user already have full access to a role, continue
            if (isset($roleAssignmentData[$roleId][$contentId])
              && $roleAssignmentData[$roleId][$contentId] instanceof RoleAssignment) {
                continue;
            }

            $limitIdentifier = $row['limit_identifier'];
            if (!empty($limitIdentifier)) {
                $roleAssignmentData[$roleId][$contentId][$limitIdentifier][$id] = new RoleAssignment(
                    [
                        'id' => $id,
                        'roleId' => $roleId,
                        'contentId' => $contentId,
                        'limitationIdentifier' => $limitIdentifier,
                        'values' => [$row['limit_value']],
                    ]
                );
            } else {
                $roleAssignmentData[$roleId][$contentId] = new RoleAssignment(
                    [
                        'id' => $id,
                        'roleId' => $roleId,
                        'contentId' => $contentId,
                    ]
                );
            }
        }

        $roleAssignments = [];
        array_walk_recursive(
            $roleAssignmentData,
            function ($roleAssignment) use (&$roleAssignments) {
                $roleAssignments[] = $roleAssignment;
            }
        );

        return $roleAssignments;
    }

    /**
     * Creates a create struct from an existing $role.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleCreateStruct
     */
    public function createCreateStructFromRole(Role $role)
    {
        $createStruct = new RoleCreateStruct();

        $createStruct->identifier = $role->identifier;
        $createStruct->policies = $role->policies;

        return $createStruct;
    }

    /**
     * Maps properties from $struct to $role.
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function createRoleFromCreateStruct(RoleCreateStruct $createStruct)
    {
        $role = new Role();

        $role->identifier = $createStruct->identifier;
        $role->policies = $createStruct->policies;
        $role->status = Role::STATUS_DRAFT;

        return $role;
    }
}
