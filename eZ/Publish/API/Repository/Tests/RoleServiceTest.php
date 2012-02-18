<?php
/**
 * File containing the RoleServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

use \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;

/**
 * Test case for operations in the RoleService using in memory storage.
 *
 * The following IDs from the default eZ community edition database are used in
 * this test:
 *
 * <ul>
 *   <li>
 *     ContentType
 *     <ul>
 *       <li><strong>28</strong>: File</li>
 *       <li><strong>29</strong>: Flash</li>
 *       <li><strong>30</strong>: Image</li>
 *     </ul>
 *   </li>
 * <ul>
 *
 * @see eZ\Publish\API\Repository\RoleService
 */
class RoleServiceTest extends BaseTest
{
    /**
     * Test for the newRoleCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::newRoleCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetRoleService
     */
    public function testNewRoleCreateStruct()
    {
        $repository = $this->getRepository();

        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'roleName' );

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\RoleCreateStruct', $roleCreate );
    }

    /**
     * Test for the newRoleCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::newRoleCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testNewRoleCreateStructSetsNamePropertyOnStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'roleName' );

        /* END: Use Case */

        $this->assertEquals( 'roleName', $roleCreate->name );
    }

    /**
     * Test for the createRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testCreateRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'roleName' );

        $role = $roleService->createRole( $roleCreate );

        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $role );
    }

    /**
     * Test for the createRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testCreateRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::createRole() is not implemented." );
    }

    /**
     * Test for the createRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testCreateRoleThrowsIllegalArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'Editor' );

        // This call will fail with an IllegalArgumentException, because Editor exists
        $roleService->createRole( $roleCreate );

        /* END: Use Case */
    }

    /**
     * Test for the loadRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testLoadRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'roleName' );

        $roleService->createRole( $roleCreate );

        // Load the newly create role by it's name
        $role = $roleService->loadRole( 'roleName' );

        /* END: Use Case */

        $this->assertEquals( 'roleName', $role->name );
    }

    /**
     * Test for the loadRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRole
     */
    public function testLoadRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::loadRole() is not implemented." );
    }

    /**
     * Test for the loadRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRole
     */
    public function testLoadRoleThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        // This call will fail with a NotFoundException, because no such role exists.
        $roleService->loadRole( 'MissingRole' );

        /* END: Use Case */
    }

    /**
     * Test for the loadRoles() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRoles()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testLoadRoles()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        // First create a custom role
        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'roleName' );

        $role = $roleService->createRole( $roleCreate );

        // Now load all available roles
        $roles = $roleService->loadRoles();

        foreach ( $roles as $role )
        {
            if ( $role->name === 'roleName' )
            {
                break;
            }
        }

        /* BEGIN: Use Case */

        $this->assertEquals( 'roleName', $role->name );
    }

    /**
     * Test for the loadRoles() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRoles()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoles
     */
    public function testLoadRolesReturnsExpectedSetOfDefaultRoles()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roles = $roleService->loadRoles();

        $roleNames = array();
        foreach ( $roles as $role )
        {
            $roleNames[] = $role->name;
        }
        /* BEGIN: Use Case */

        sort( $roleNames );

        $this->assertEquals(
            array(
                'Administrator',
                'Anonymous',
                'Editor',
                'Member',
                'Partner'
            ),
            $roleNames
        );
    }

    /**
     * Test for the loadRoles() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRoles()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadRolesThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::loadRoles() is not implemented." );
    }

    /**
     * Test for the newRoleUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::newRoleUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetRoleService
     */
    public function testNewRoleUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleUpdate  = $roleService->newRoleUpdateStruct( 'newRole' );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\RoleUpdateStruct', $roleUpdate );
    }

    /**
     * Test for the updateRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updateRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRole
     */
    public function testUpdateRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'newRole' );

        $role = $roleService->createRole( $roleCreate );

        $roleUpdate       = $roleService->newRoleUpdateStruct();
        $roleUpdate->name = 'updatedRole';

        $updatedRole = $roleService->updateRole( $role, $roleUpdate );
        /* END: Use Case */

        // Now verify that our change was saved
        $role = $roleService->loadRole( 'updatedRole' );

        $this->assertEquals( $role->id, $updatedRole->id );
    }

    /**
     * Test for the updateRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updateRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdateRole
     */
    public function testUpdateRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::updateRole() is not implemented." );
    }

    /**
     * Test for the updateRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updateRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdateRole
     */
    public function testUpdateRoleThrowsIllegalArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'newRole' );

        $role = $roleService->createRole( $roleCreate );

        $roleUpdate       = $roleService->newRoleUpdateStruct();
        $roleUpdate->name = 'Editor';

        // This call will fail with an IllegalArgumentException, because Editor is a predefined role
        $roleService->updateRole( $role, $roleUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the deleteRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::deleteRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoles
     */
    public function testDeleteRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate  = $roleService->newRoleCreateStruct( 'newRole' );

        $role = $roleService->createRole( $roleCreate );

        $roleService->deleteRole( $role );
        /* END: Use Case */

        $this->assertEquals( 5, count( $roleService->loadRoles() ) );
    }

    /**
     * Test for the deleteRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::deleteRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testDeleteRole
     */
    public function testDeleteRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::deleteRole() is not implemented." );
    }

    /**
     * Test for the newPolicyCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::newPolicyCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetRoleService
     */
    public function testNewPolicyCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService  = $repository->getRoleService();
        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\PolicyCreateStruct', $policyCreate );
    }

    /**
     * Test for the newPolicyCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::newPolicyCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyCreateStruct
     */
    public function testNewPolicyCreateStructSetsStructProperties()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService  = $repository->getRoleService();
        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        /* END: Use Case */

        $this->assertEquals(
            array( 'content', 'create' ),
            array( $policyCreate->module, $policyCreate->function )
        );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRole
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyCreateStruct
     */
    public function testAddPolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );
        $role       = $roleService->createRole( $roleCreate );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $role         = $roleService->addPolicy( $role, $policyCreate );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $role );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicy
     */
    public function testAddPolicyUpdatesRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );
        $role       = $roleService->createRole( $roleCreate );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $role         = $roleService->addPolicy( $role, $policyCreate );

        $policy = $role->getPolicy( 'content', 'create' );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Policy', $policy );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyUpdatesRole
     */
    public function testAddPolicySetsPolicyProperties()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );
        $role       = $roleService->createRole( $roleCreate );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $role         = $roleService->addPolicy( $role, $policyCreate );

        $policy = $role->getPolicy( 'content', 'create' );
        /* END: Use Case */

        $this->assertEquals(
            array( $role->id, 'content', 'create' ),
            array( $policy->roleId, $policy->module, $policy->function )
        );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAddPolicyThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::addPolicy() is not implemented." );
    }

    /**
     * Test for the createRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyUpdatesRole
     */
    public function testCreateRoleWithAddPolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );
        $roleCreate->addPolicy( $roleService->newPolicyCreateStruct( 'content', 'read' ) );
        $roleCreate->addPolicy( $roleService->newPolicyCreateStruct( 'content', 'translate' ) );

        $role = $roleService->createRole( $roleCreate );

        $policies = array();
        foreach ( $role->getPolicies() as $policy )
        {
            $policies[] = array( 'module' => $policy->module, 'function' => $policy->function );
        }
        /* END: Use Case */

        $this->assertEquals(
            array(
                array(
                    'module'    =>  'content',
                    'function'  =>  'read'
                ),
                array(
                    'module'    =>  'content',
                    'function'  =>  'translate'
                )
            ),
            $policies
        );
    }

    /**
     * Test for the newPolicyUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::newPolicyUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetRoleService
     */
    public function testNewPolicyUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService  = $repository->getRoleService();
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct', $policyUpdate );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicy
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyUpdateStruct
     */
    public function testUpdatePolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService  = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'translate' );
        $policyCreate->addLimitation( new LanguageLimitation( array( 'limitationValues' => array( 28, 29 ) ) ) );

        $roleCreate = $roleService->newRoleCreateStruct( 'myRole' );
        $roleCreate->addPolicy( $policyCreate );

        $role = $roleService->createRole( $roleCreate );

        $policy = $role->getPolicy( 'content', 'translate' );

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation( new ContentTypeLimitation( array( 'limitationValues' => array( 29, 30 ) ) ) );

        $policy = $roleService->updatePolicy( $policy, $policyUpdate );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Policy', $policy );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdatePolicy
     */
    public function testUpdatePolicyUpdatesLimitations()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService  = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'translate' );
        $policyCreate->addLimitation( new LanguageLimitation( array( 'limitationValues' => array( 28, 29 ) ) ) );

        $roleCreate = $roleService->newRoleCreateStruct( 'myRole' );
        $roleCreate->addPolicy( $policyCreate );

        $role = $roleService->createRole( $roleCreate );

        $policy = $role->getPolicy( 'content', 'translate' );

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation( new ContentTypeLimitation( array( 'limitationValues' => array( 29, 30 ) ) ) );

        $policy = $roleService->updatePolicy( $policy, $policyUpdate );
        /* END: Use Case */

        $this->assertEquals(
            array( new ContentTypeLimitation( array( 'limitationValues' => array( 29, 30 ) ) ) ),
            $policy->getLimitations()
        );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdatePolicyUpdatesLimitations
     */
    public function testUpdatePolicyUpdatesRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService  = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'translate' );
        $policyCreate->addLimitation( new LanguageLimitation( array( 'limitationValues' => array( 28, 29 ) ) ) );

        $roleCreate = $roleService->newRoleCreateStruct( 'myRole' );
        $roleCreate->addPolicy( $policyCreate );

        $role = $roleService->createRole( $roleCreate );

        $policy = $role->getPolicy( 'content', 'translate' );

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation( new ContentTypeLimitation( array( 'limitationValues' => array( 29, 30 ) ) ) );

        $roleService->updatePolicy( $policy, $policyUpdate );

        $role = $roleService->loadRole( 'myRole' );

        $policy = $role->getPolicy( 'content', 'translate' );
        /* END: Use Case */

        $this->assertEquals(
            array( new ContentTypeLimitation( array( 'limitationValues' => array( 29, 30 ) ) ) ),
            $policy->getLimitations()
        );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdatePolicyThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::updatePolicy() is not implemented." );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::removePolicy()
     * 
     */
    public function testRemovePolicy()
    {
        $this->markTestIncomplete( "Test for RoleService::removePolicy() is not implemented." );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::removePolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemovePolicyThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::removePolicy() is not implemented." );
    }

    /**
     * Test for the loadPoliciesByUserId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId()
     * 
     */
    public function testLoadPoliciesByUserId()
    {
        $this->markTestIncomplete( "Test for RoleService::loadPoliciesByUserId() is not implemented." );
    }

    /**
     * Test for the loadPoliciesByUserId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadPoliciesByUserIdThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for RoleService::loadPoliciesByUserId() is not implemented." );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup()
     * 
     */
    public function testAssignRoleToUserGroup()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUserGroup() is not implemented." );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * 
     */
    public function testAssignRoleToUserGroupWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUserGroup() is not implemented." );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUserGroup() is not implemented." );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUserGroup() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup()
     * 
     */
    public function testUnassignRoleFromUserGroup()
    {
        $this->markTestIncomplete( "Test for RoleService::unassignRoleFromUserGroup() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnassignRoleFromUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::unassignRoleFromUserGroup() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUnassignRoleFromUserGroupThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "Test for RoleService::unassignRoleFromUserGroup() is not implemented." );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser()
     * 
     */
    public function testAssignRoleToUser()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUser() is not implemented." );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * 
     */
    public function testAssignRoleToUserWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUser() is not implemented." );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUser() is not implemented." );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for RoleService::assignRoleToUser() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser()
     * 
     */
    public function testUnassignRoleFromUser()
    {
        $this->markTestIncomplete( "Test for RoleService::unassignRoleFromUser() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnassignRoleFromUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::unassignRoleFromUser() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUnassignRoleFromUserThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "Test for RoleService::unassignRoleFromUser() is not implemented." );
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * 
     */
    public function testGetRoleAssignments()
    {
        $this->markTestIncomplete( "Test for RoleService::getRoleAssignments() is not implemented." );
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testGetRoleAssignmentsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::getRoleAssignments() is not implemented." );
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * 
     */
    public function testGetRoleAssignmentsForUser()
    {
        $this->markTestIncomplete( "Test for RoleService::getRoleAssignmentsForUser() is not implemented." );
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testGetRoleAssignmentsForUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::getRoleAssignmentsForUser() is not implemented." );
    }

    /**
     * Test for the getRoleAssignmentsForUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup()
     * 
     */
    public function testGetRoleAssignmentsForUserGroup()
    {
        $this->markTestIncomplete( "Test for RoleService::getRoleAssignmentsForUserGroup() is not implemented." );
    }

    /**
     * Test for the getRoleAssignmentsForUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testGetRoleAssignmentsForUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for RoleService::getRoleAssignmentsForUserGroup() is not implemented." );
    }
}
