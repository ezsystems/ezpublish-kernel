<?php
/**
 * File containing the User mapper
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User;
use eZ\Publish\SPI\Persistence\User,
    eZ\Publish\SPI\Persistence\User\Role,
    eZ\Publish\SPI\Persistence\User\RoleUpdateStruct,
    eZ\Publish\SPI\Persistence\User\Policy,
    RuntimeException;

/**
 * mapper for User realted objects
 *
 */
class Mapper
{
    /**
     * Map user data into user object
     *
     * @param array $data
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
     * @return \eZ\Publish\SPI\Persistence\User\User[]
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
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function mapPolicies( array $data )
    {
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
                    )
                );
            }

            if ( !$row['ezpolicy_limitation_identifier'] )
            {
                continue;
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
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function mapRole( array $data )
    {
        $role = new Role();

        foreach ( $data as $row )
        {
            if ( empty( $role->id ) )
            {
                $role->id = $row['ezrole_id'];
                $role->identifier = $row['ezrole_name'];
                // skip name and description as they don't exist in legacy
            }

            $role->groupIds[] = $row['ezuser_role_contentobject_id'];
        }

        // Remove dublicates and santitize arrays
        $role->groupIds = array_values( array_unique( array_filter( $role->groupIds ) ) );
        $role->policies = $this->mapPolicies( $data );

        return $role;
    }

    /**
     * Map data for a set of roles
     *
     * @param array $data
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function mapRoles( array $data )
    {
        $roleData = array();
        foreach ( $data as $row )
        {
            $roleData[$row['ezrole_id']][] = $row;
        }

        $roles = array();
        foreach ( $roleData as $data )
        {
            $roles[] = $this->mapRole( $data );
        }

        return $roles;
    }
}
