<?php

/**
 * File containing the RoleServiceAuthorizationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;

/**
 * Test case for operations in the RoleService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\RoleService
 * @group integration
 * @group authorization
 */
class RoleServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateRoleThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // Get the role service
        $roleService = $repository->getRoleService();

        // Instantiate a role create struct.
        $roleCreate = $roleService->newRoleCreateStruct('roleName');

        // This call will fail with an "UnauthorizedException"
        $roleService->createRole($roleCreate);
        /* END: Use Case */
    }

    /**
     * Test for the loadRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRole
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadRoleThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->loadRole($role->id);
        /* END: Use Case */
    }

    /**
     * Test for the loadRoleByIdentifier() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadRoleByIdentifierThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->loadRoleByIdentifier($role->identifier);
        /* END: Use Case */
    }

    /**
     * Test for the loadRoles() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::loadRoles()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoles
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadRolesThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // Get the role service
        $roleService = $repository->getRoleService();

        // This call will fail with an "UnauthorizedException"
        $roleService->loadRoles();
        /* END: Use Case */
    }

    /**
     * Test for the updateRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::updateRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdateRole
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testUpdateRoleThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // Get a new role update struct and set new values
        $roleUpdateStruct = $roleService->newRoleUpdateStruct();

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleUpdateStruct->mainLanguageCode = 'eng-US';

        // This call will fail with an "UnauthorizedException"
        $roleService->updateRole($role, $roleUpdateStruct);
        /* END: Use Case */
    }

    /**
     * Test for the deleteRole() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::deleteRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testDeleteRole
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testDeleteRoleThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->deleteRole($role);
        /* END: Use Case */
    }

    /**
     * Test for the addPolicy() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicy
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testAddPolicyThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct('content', 'delete')
        );
        /* END: Use Case */
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdatePolicy
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testUpdatePolicyThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Get first role policy
        $policies = $role->getPolicies();
        $policy = reset($policies);

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // Get a policy update struct and add a limitation
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new SubtreeLimitation(
                array(
                    'limitationValues' => array('/1/'),
                )
            )
        );

        // This call will fail with an "UnauthorizedException"
        $roleService->updatePolicy($policy, $policyUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the removePolicy() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::removePolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testRemovePolicy
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testRemovePolicyThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleCreate = $roleService->newRoleCreateStruct('newRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Create a new role with two policies
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'create')
        );
        $roleDraft = $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $roleService->newPolicyCreateStruct('content', 'delete')
        );

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->removePolicyByRoleDraft($roleDraft, $roleDraft->getPolicies()[0]);
        /* END: Use Case */
    }

    /**
     * Test for the deletePolicy() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::deletePolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testDeletePolicy
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testDeletePolicyThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Get first role policy
        $policies = $role->getPolicies();
        $policy = reset($policies);

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->deletePolicy($policy);
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Load the "Editors" user group
        $userGroup = $userService->loadUserGroup($editorsGroupId);

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->assignRoleToUserGroup($role, $userGroup);
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedExceptionWithRoleLimitationParameter()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Load the "Editors" user group
        $userGroup = $userService->loadUserGroup($editorsGroupId);

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // Create a subtree role limitation
        $limitation = new SubtreeLimitation(
            array(
                'limitationValues' => array('/1/2/'),
            )
        );

        // This call will fail with an "UnauthorizedException"
        $roleService->assignRoleToUserGroup($role, $userGroup, $limitation);
        /* END: Use Case */
    }

    /**
     * Test for the unassignRoleFromUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUnassignRoleFromUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testUnassignRoleFromUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Load the "Editors" user group
        $userGroup = $userService->loadUserGroup($editorsGroupId);

        // Assign new role to "Editors" user group
        $roleService->assignRoleToUserGroup($role, $userGroup);

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->unassignRoleFromUserGroup($role, $userGroup);
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testAssignRoleToUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->assignRoleToUser($role, $user);
        /* END: Use Case */
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testAssignRoleToUserThrowsUnauthorizedExceptionWithRoleLimitationParameter()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // Create a subtree role limitation
        $limitation = new SubtreeLimitation(
            array(
                'limitationValues' => array('/1/2/'),
            )
        );

        // This call will fail with an "UnauthorizedException"
        $roleService->assignRoleToUser($role, $user, $limitation);
        /* END: Use Case */
    }

    /**
     * Test for the unassignRoleFromUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUnassignRoleFromUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testUnassignRoleFromUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Assign new role to "Editor" user
        $roleService->assignRoleToUser($role, $user);

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->unassignRoleFromUser($role, $user);
        /* END: Use Case */
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignments
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testGetRoleAssignmentsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $role = $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->getRoleAssignments($role);
        /* END: Use Case */
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignmentsForUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testGetRoleAssignmentsForUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $this->createRole();

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->getRoleAssignmentsForUser($user);
        /* END: Use Case */
    }

    /**
     * Test for the getRoleAssignmentsForUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignmentsForUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testGetRoleAssignmentsForUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $this->createRole();

        // Load the "Editors" user group
        $userGroup = $userService->loadUserGroup($editorsGroupId);

        // Set "Editor" user as current user.
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $roleService->getRoleAssignmentsForUserGroup($userGroup);
        /* END: Use Case */
    }

    /**
     * Create a role fixture in a variable named <b>$role</b>,.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    private function createRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // Get the role service
        $roleService = $repository->getRoleService();

        // Get new policy create struct
        $policyCreate = $roleService->newPolicyCreateStruct('content', '*');

        // Get a role create struct instance and set properties
        $roleCreate = $roleService->newRoleCreateStruct('testRole');

        // @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        // $roleCreate->mainLanguageCode = 'eng-GB';

        $roleCreate->addPolicy($policyCreate);

        // Create a new role instance.
        $roleDraft = $roleService->createRole($roleCreate);
        $roleService->publishRoleDraft($roleDraft);
        $role = $roleService->loadRole($roleDraft->id);
        /* END: Inline */

        return $role;
    }
}
