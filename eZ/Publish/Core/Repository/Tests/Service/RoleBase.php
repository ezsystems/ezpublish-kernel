<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\RoleBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;

use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,

    eZ\Publish\Core\Repository\Values\User\Role,
    eZ\Publish\Core\Repository\Values\User\Policy,

    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission;

/**
 * Test case for Role Service
 *
 */
abstract class RoleBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\User\Role::__construct
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__construct
     */
    public function testNewClass()
    {
        $role = new Role();
        self::assertEquals( null, $role->id );
        self::assertEquals( null, $role->identifier );
        self::assertInternalType( "array", $role->policies );
        self::assertEmpty( $role->policies );

        $policy = new Policy();
        self::assertEquals( null, $policy->id );
        self::assertEquals( null, $policy->roleId );
        self::assertEquals( null, $policy->module );
        self::assertEquals( null, $policy->function );
        self::assertInternalType( "array", $policy->limitations );
        self::assertEmpty( $policy->limitations );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\User\Role::__get
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $role = new Role();
            $value = $role->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch( PropertyNotFound $e ) {}

        try
        {
            $policy = new Policy();
            $value = $policy->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch( PropertyNotFound $e ) {}
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\User\Role::__set
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $role = new Role();
            $role->policies = array();
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyPermission $e ) {}

        try
        {
            $policy = new Policy();
            $policy->limitations = array();
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyPermission $e ) {}
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\User\Role::__isset
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__isset
     */
    public function testIsPropertySet()
    {
        $role = new Role();
        $value = isset( $role->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $role->id );
        self::assertEquals( true, $value );

        $policy = new Policy();
        $value = isset( $policy->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $policy->id );
        self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\User\Role::__unset
     * @covers \eZ\Publish\API\Repository\Values\User\Policy::__unset
     */
    public function testUnsetProperty()
    {
        $role = new Role( array( "id" => 1 ) );
        try
        {
            unset( $role->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyPermission $e ) {}

        $policy = new Policy( array( "id" => 1 ) );
        try
        {
            unset( $policy->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyPermission $e ) {}
    }

    /**
     * Test creating a role with existing name
     * @expectedException eZ\Publish\Core\Base\Exceptions\IllegalArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRoleWithExistingName()
    {
        self::markTestSkipped( "@todo: enable when RoleService::loadRole (loading role by name) implementation is done" );
        $roleService = $this->repository->getRoleService();
        $roleCreateStruct = $roleService->newRoleCreateStruct( "Anonymous" );

        $roleService->createRole( $roleCreateStruct );
    }

    /**
     * Test creating a role
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRole()
    {
        $roleService = $this->repository->getRoleService();
        $roleCreateStruct = $roleService->newRoleCreateStruct( "Ultimate permissions" );

        $createdRole = $roleService->createRole( $roleCreateStruct );

        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" , $createdRole );
        self::assertGreaterThan( 0, $createdRole->id );
        self::assertEquals( $roleCreateStruct->identifier, $createdRole->identifier );
        self::assertEmpty( $createdRole->getPolicies() );
    }

    /**
     * Test creating a role with policies
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRoleWithPolicies()
    {
        $roleService = $this->repository->getRoleService();

        $limitation1 = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $limitation1->limitationValues = array( '12', '13', '14' );

        $limitation2 = new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();
        $limitation2->limitationValues = array( '5', '6' );

        $limitation3 = new \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation();
        $limitation3->limitationValues = array( '1' );

        $limitation4 = new \eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation();
        $limitation4->limitationValues = array( '1', '2' );

        $policyCreateStruct1 = $roleService->newPolicyCreateStruct( 'content', 'read' );
        $policyCreateStruct1->addLimitation( $limitation1 );
        $policyCreateStruct1->addLimitation( $limitation2 );

        $policyCreateStruct2 = $roleService->newPolicyCreateStruct( 'content', 'edit' );
        $policyCreateStruct2->addLimitation( $limitation3 );
        $policyCreateStruct2->addLimitation( $limitation4 );

        $roleCreateStruct = $roleService->newRoleCreateStruct( "Ultimate permissions" );
        $roleCreateStruct->addPolicy( $policyCreateStruct1 );
        $roleCreateStruct->addPolicy( $policyCreateStruct2 );

        $createdRole = $roleService->createRole( $roleCreateStruct );

        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" , $createdRole );
        self::assertGreaterThan( 0, $createdRole->id );
        self::assertEquals( $roleCreateStruct->identifier, $createdRole->identifier );

        self::assertCount( 2, $createdRole->getPolicies() );
    }

    /**
     * Test updating role with existing name
     * @expectedException eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::updateRole
     */
    public function testUpdateRoleWithExistingName()
    {
        self::markTestSkipped( "@todo: enable when RoleService::loadRole (loading role by name) implementation is done" );
        $roleService = $this->repository->getRoleService();

        $role = new Role( array( "id" => 2 ) );

        $roleUpdateStruct = $roleService->newRoleUpdateStruct();
        $roleUpdateStruct->identifier = "Anonymous";

        $roleService->updateRole( $role, $roleUpdateStruct );
    }

    /**
     * Test updating role
     * @covers \eZ\Publish\API\Repository\RoleService::updateRole
     */
    public function testUpdateRole()
    {
        $roleService = $this->repository->getRoleService();

        $role = new Role( array( "id" => 1 ) );

        $roleUpdateStruct = $roleService->newRoleUpdateStruct();
        $roleUpdateStruct->identifier = "Anonymous 2";

        $updatedRole = $roleService->updateRole( $role, $roleUpdateStruct );
        self::assertEquals( $role->id, $updatedRole->id );
        self::assertEquals( $roleUpdateStruct->identifier, $updatedRole->identifier );
    }

    /**
     * Test adding policy to role with invalid values
     * @expectedException eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::addPolicy
     */
    public function testAddPolicyWithInvalidValues()
    {
        $roleService = $this->repository->getRoleService();

        $role = new Role( array( "id" => 1 ) );

        $limitation1 = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $limitation1->limitationValues = array( '12', '13', '14' );

        $limitation2 = new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();
        $limitation2->limitationValues = array( '5', '6' );

        // policy with "module: *, function: anything but *" is not semantically correct
        $policyCreateStruct = $roleService->newPolicyCreateStruct( '*', 'read' );
        $policyCreateStruct->addLimitation( $limitation1 );
        $policyCreateStruct->addLimitation( $limitation2 );

        $roleService->addPolicy( $role, $policyCreateStruct );
    }

    /**
     * Test adding policy to role
     * @covers \eZ\Publish\API\Repository\RoleService::addPolicy
     */
    public function testAddPolicy()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        $policyCount = count( $role->getPolicies() );

        $limitation1 = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $limitation1->limitationValues = array( '12', '13', '14' );

        $limitation2 = new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();
        $limitation2->limitationValues = array( '5', '6' );

        $policyCreateStruct = $roleService->newPolicyCreateStruct( 'content', 'read' );
        $policyCreateStruct->addLimitation( $limitation1 );
        $policyCreateStruct->addLimitation( $limitation2 );

        $role = $roleService->addPolicy( $role, $policyCreateStruct );

        self::assertCount( $policyCount + 1, $role->getPolicies() );
    }

    /**
     * Test removing policy from the role
     * @covers \eZ\Publish\API\Repository\RoleService::removePolicy
     */
    public function testRemovePolicy()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        $policyCount = count( $role->getPolicies() );

        $limitation1 = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $limitation1->limitationValues = array( '12', '13', '14' );

        $limitation2 = new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();
        $limitation2->limitationValues = array( '5', '6' );

        $policyCreateStruct = $roleService->newPolicyCreateStruct( 'content', 'read' );
        $policyCreateStruct->addLimitation( $limitation1 );
        $policyCreateStruct->addLimitation( $limitation2 );

        $role = $roleService->addPolicy( $role, $policyCreateStruct );
        self::assertCount( $policyCount + 1, $role->getPolicies() );

        $policyToRemove = $role->policies[0];
        $role = $roleService->removePolicy( $role, $policyToRemove );
        self::assertCount( $policyCount, $role->getPolicies() );
    }

    /**
     * Test updating policies of a role, depends on proper eZ Publish clean data
     * which has a number of policies and limitations applied to Anonymous role
     * @covers \eZ\Publish\API\Repository\RoleService::updatePolicy
     */
    public function testUpdatePolicy()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        $policy = $role->policies[2];

        $limitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $limitation->limitationValues = array( '12', '13', '14' );

        $policyUpdateStruct = $roleService->newPolicyUpdateStruct();
        $policyUpdateStruct->addLimitation( $limitation );

        $policy = $roleService->updatePolicy( $policy, $policyUpdateStruct );
        self::assertCount( 1, $policy->getLimitations() );
    }

    /**
     * Test loading role by name
     * @covers \eZ\Publish\API\Repository\RoleService::loadRole
     */
    public function testLoadRole()
    {
        self::markTestSkipped( "@todo: enable when RoleService::loadRole (loading role by name) implementation is done" );
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( "Anonymous" );
        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" , $role );
        self::assertGreaterThan( 0, $role->id );
        self::assertEquals( "Anonymous", $role->identifier );
    }

    /**
     * Test loading all roles
     * @covers \eZ\Publish\API\Repository\RoleService::loadRoles
     */
    public function testLoadAllRoles()
    {
        $roleService = $this->repository->getRoleService();

        $roles = $roleService->loadRoles();
        self::assertInternalType( "array", $roles );
        self::assertGreaterThan( 0, count( $roles ) );
    }

    /**
     * Test deleting a role
     * @expectedException eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\RoleService::deleteRole
     */
    public function testDeleteRole()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" , $role );

        $roleService->deleteRole( $role );

        $roleService->loadRole( 1 );
    }

    /**
     * Test loading policies by non existing user ID
     * @expectedException eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId
     */
    public function testLoadPoliciesByNonExistingUserId()
    {
        self::markTestSkipped( "@todo: enable when UserService::loadUser implementation is done" );
        $roleService = $this->repository->getRoleService();

        $roleService->loadPoliciesByUserId( PHP_INT_MAX );
    }

    /**
     * Test loading policies by user ID
     * @covers \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId
     */
    public function testLoadPoliciesByUserId()
    {
        self::markTestSkipped( "@todo: enable when UserService::loadUser implementation is done" );
        $roleService = $this->repository->getRoleService();

        $policies = $roleService->loadPoliciesByUserId( 10 );
        self::assertGreaterThan( 0, count( $policies ) );
    }

    /**
     * Test assigning role to user group
     * @covers \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup
     */
    public function testAssignRoleToUserGroup()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test assigning role to user group which is not already assigned to the group
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup
     */
    public function testUnassignNotAssignedRoleFromUserGroup()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test assigning role to user group
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup
     */
    public function testUnassignRoleFromUserGroup()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test assigning role to user
     * @covers \eZ\Publish\API\Repository\RoleService::assignRoleToUser
     */
    public function testAssignRoleToUser()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test assigning role to user which is not already assigned to the user
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser
     */
    public function testUnassignNotAssignedRoleFromUser()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test assigning role to user
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser
     */
    public function testUnassignRoleFromUser()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test fetching all role assignments for specified role
     * @covers \eZ\Publish\API\Repository\RoleService::getRoleAssignments
     */
    public function testGetRoleAssignments()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test fetching role assignments for specified user
     * @covers \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser
     */
    public function testGetRoleAssignmentsForUser()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test fetching role assignments for specified user group
     * @covers \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup
     */
    public function testGetRoleAssignmentsForUserGroup()
    {
        self::markTestIncomplete( "@todo: dependency on user service" );
    }

    /**
     * Test creating new RoleCreateStruct
     * @covers \eZ\Publish\API\Repository\RoleService::newRoleCreateStruct
     */
    public function testNewRoleCreateStruct()
    {
        $roleService = $this->repository->getRoleService();

        $roleCreateStruct = $roleService->newRoleCreateStruct( "Ultimate permissions" );
        self::assertEquals( "Ultimate permissions", $roleCreateStruct->identifier );
        self::assertInternalType( "array", $roleCreateStruct->getPolicies() );
        self::assertEmpty( $roleCreateStruct->getPolicies() );
    }

    /**
     * Test creating new PolicyCreateStruct
     * @covers \eZ\Publish\API\Repository\RoleService::newPolicyCreateStruct
     */
    public function testNewPolicyCreateStruct()
    {
        $roleService = $this->repository->getRoleService();

        $policyCreateStruct = $roleService->newPolicyCreateStruct( "content", "read" );
        self::assertEquals( "content", $policyCreateStruct->module );
        self::assertEquals( "read", $policyCreateStruct->function );
        self::assertInternalType( "array", $policyCreateStruct->getLimitations() );
        self::assertEmpty( $policyCreateStruct->getLimitations() );
    }

    /**
     * Test creating new RoleUpdateStruct
     * @covers \eZ\Publish\API\Repository\RoleService::newRoleUpdateStruct
     */
    public function testNewRoleUpdateStruct()
    {
        $roleService = $this->repository->getRoleService();

        $roleUpdateStruct = $roleService->newRoleUpdateStruct();
        self::assertNull( $roleUpdateStruct->identifier );
    }

    /**
     * Test creating new PolicyUpdateStruct
     * @covers \eZ\Publish\API\Repository\RoleService::newPolicyUpdateStruct
     */
    public function testNewPolicyUpdateStruct()
    {
        $roleService = $this->repository->getRoleService();

        $policyUpdateStruct = $roleService->newPolicyUpdateStruct();
        self::assertInternalType( "array", $policyUpdateStruct->getLimitations() );
        self::assertEmpty( $policyUpdateStruct->getLimitations() );
    }
}
