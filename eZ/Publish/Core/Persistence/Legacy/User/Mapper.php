<?php
/**
 * File containing the User mapper
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;

/**
 * mapper for User related objects
 */
class Mapper
{
    /**
     * Map user data into user object
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function mapUser( array $data )
    {
        $user = new User();
        $user->id = $data['contentobject_id'];
        $user->login = $data['login'];
        $user->email = $data['email'];
        $user->passwordHash = $data['password_hash'];
        $user->hashAlgorithm = $data['password_hash_type'];
        $user->isEnabled = (bool)$data['is_enabled'];
        $user->maxLogin = $data['max_login'];

        return $user;
    }

    /**
     * Map data for a set of user data
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function mapUsers( array $data )
    {
        $users = array();
        foreach ( $data as $row )
        {
            $users[] = $this->mapUser( $row );
        }

        return $users;
    }

    /**
     * Map policy data to an array of policies
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function mapPolicies( array $data )
    {
        /**
         * @var \eZ\Publish\SPI\Persistence\User\Policy[] $policies
         */
        $policies = array();
        foreach ( $data as $row )
        {
            $policyId = $row['ezpolicy_id'];
            if ( !isset( $policies[$policyId] ) &&
                 ( $policyId !== null ) )
            {
                $policies[$policyId] = new Policy(
                    array(
                        'id' => $row['ezpolicy_id'],
                        'roleId' => $row['ezrole_id'],
                        'module' => $row['ezpolicy_module_name'],
                        'function' => $row['ezpolicy_function_name'],
                        'limitations' => '*' // limitations must be '*' if not a non empty array of limitations
                    )
                );
            }

            if ( !$row['ezpolicy_limitation_identifier'] )
            {
                continue;
            }
            else if ( $policies[$policyId]->limitations === '*' )
            {
                $policies[$policyId]->limitations = array();
            }

            if ( !isset( $policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']] ) )
            {
                $policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']] = array( $row['ezpolicy_limitation_value_value'] );
            }
            else if ( !in_array( $row['ezpolicy_limitation_value_value'], $policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']] ) )
            {
                $policies[$policyId]->limitations[$row['ezpolicy_limitation_identifier']][] = $row['ezpolicy_limitation_value_value'];
            }
        }

        return array_values( $policies );
    }

    /**
     * Map role data to a role
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function mapRole( array $data )
    {
        $role = new Role();

        foreach ( $data as $row )
        {
            if ( empty( $role->id ) )
            {
                $role->id = (int)$row['ezrole_id'];
                $role->identifier = $row['ezrole_name'];
                // skip name and description as they don't exist in legacy
            }

            $role->groupIds[] = $row['ezuser_role_contentobject_id'];
        }

        // Remove duplicates and sanitize arrays
        $role->groupIds = array_values( array_unique( array_filter( $role->groupIds ) ) );
        $role->policies = $this->mapPolicies( $data );

        return $role;
    }

    /**
     * Map data for a set of roles
     *
     * @param array $data
     * @param boolean $indexById
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function mapRoles( array $data, $indexById = false )
    {
        $roleData = array();
        foreach ( $data as $row )
        {
            $roleData[$row['ezrole_id']][] = $row;
        }

        $roles = array();
        foreach ( $roleData as $id => $data )
        {
            if ( $indexById )
                $roles[$id] = $this->mapRole( $data );
            else
                $roles[] = $this->mapRole( $data );
        }

        return $roles;
    }

    /**
     * Map data for a set of role assignments
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function mapRoleAssignments( array $data, array $roleData )
    {
        $roles = $this->mapRoles( $roleData, true );

        $roleAssignmentData = array();
        foreach ( $data as $row )
        {
            $roleId = (int)$row['role_id'];
            $contentId = (int)$row['contentobject_id'];
             // if user already have full access to a role, continue
            if ( isset( $roleAssignmentData[$roleId][$contentId] )
              && $roleAssignmentData[$roleId][$contentId] instanceof RoleAssignment )
                continue;

            $limitIdentifier = $row['limit_identifier'];
            if ( !empty( $limitIdentifier ) )
            {
                if ( !isset( $roleAssignmentData[$roleId][$contentId][$limitIdentifier] ) )
                {
                    $roleAssignmentData[$roleId][$contentId][$limitIdentifier] = new RoleAssignment(
                        array(
                            'role' => $roles[$roleId],
                            'contentId' => $contentId,
                            'limitationIdentifier' => $limitIdentifier,
                            'values' => array( $row['limit_value'] )
                        )
                    );
                }
                else
                {
                    $roleAssignmentData[$roleId][$contentId][$limitIdentifier]->values[] = $row['limit_value'];
                }
            }
            else
            {
                $roleAssignmentData[$roleId][$contentId] = new RoleAssignment(
                    array(
                        'role' => $roles[$roleId],
                        'contentId' => $contentId
                    )
                );
            }
        }

        $roleAssignments = array();
        array_walk_recursive(
            $roleAssignmentData,
            function ( $roleAssignment ) use ( &$roleAssignments )
            {
                $roleAssignments[] = $roleAssignment;
            }
        );

        return $roleAssignments;
    }
}
