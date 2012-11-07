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
    eZ\Publish\API\Repository\Values\User\Limitation,

    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound,
    eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException,
    eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for Role Service
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
        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'identifier' => null,
                // @todo uncomment when support for multilingual names and descriptions is added
                // 'mainLanguageCode' => null,
                'policies' => array()
            ),
            new Role()
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'roleId' => null,
                'module' => null,
                'function' => null,
                'limitations' => array()
            ),
            new Policy()
        );
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
            $role->id = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyReadOnlyException $e ) {}

        try
        {
            $policy = new Policy();
            $policy->id = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyReadOnlyException $e ) {}
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
        catch ( PropertyReadOnlyException $e ) {}

        $policy = new Policy( array( "id" => 1 ) );
        try
        {
            unset( $policy->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e ) {}
    }

    /**
     * Test creating a role
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRole()
    {
        $roleService = $this->repository->getRoleService();
        $roleCreateStruct = $roleService->newRoleCreateStruct( 'ultimate_permissions' );
        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreateStruct->mainLanguageCode = 'eng-GB';
        // $roleCreateStruct->names = array( 'eng-GB' => 'Ultimate permissions' );
        // $roleCreateStruct->descriptions = array( 'eng-GB' => 'This is a role with ultimate permissions' );

        $createdRole = $roleService->createRole( $roleCreateStruct );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $createdRole );
        self::assertGreaterThan( 0, $createdRole->id );

        /* @todo uncomment when support for multilingual names and descriptions is added
        self::assertEquals(
            array(
                'eng-GB' => $roleCreateStruct->names['eng-GB']
            ),
            $createdRole->getNames()
        );

        self::assertEquals(
            array(
                'eng-GB' => $roleCreateStruct->descriptions['eng-GB']
            ),
            $createdRole->getDescriptions()
        );
        */

        $this->assertPropertiesCorrect(
            array(
                'identifier' => $roleCreateStruct->identifier,
                'policies' => array()
            ),
            $createdRole
        );
    }

    /**
     * Test creating a role throwing InvalidArgumentException
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRoleThrowsInvalidArgumentException()
    {
        $roleService = $this->repository->getRoleService();
        $roleCreateStruct = $roleService->newRoleCreateStruct( 'Anonymous' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreateStruct->mainLanguageCode = 'eng-GB';
        // $roleCreateStruct->names = array( 'eng-GB' => 'Anonymous' );
        // $roleCreateStruct->descriptions = array( 'eng-GB' => 'Anonymous role' );

        $roleService->createRole( $roleCreateStruct );
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

        $roleCreateStruct = $roleService->newRoleCreateStruct( 'ultimate_permissions' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreateStruct->mainLanguageCode = 'eng-GB';
        // $roleCreateStruct->names = array( 'eng-GB' => 'Ultimate permissions' );
        // $roleCreateStruct->descriptions = array( 'eng-GB' => 'This is a role with ultimate permissions' );

        $roleCreateStruct->addPolicy( $policyCreateStruct1 );
        $roleCreateStruct->addPolicy( $policyCreateStruct2 );

        $createdRole = $roleService->createRole( $roleCreateStruct );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $createdRole );
        self::assertGreaterThan( 0, $createdRole->id );

        /* @todo uncomment when support for multilingual names and descriptions is added
        self::assertEquals(
            array(
                'eng-GB' => $roleCreateStruct->names['eng-GB']
            ),
            $createdRole->getNames()
        );

        self::assertEquals(
            array(
                'eng-GB' => $roleCreateStruct->descriptions['eng-GB']
            ),
            $createdRole->getDescriptions()
        );
        */

        $this->assertPropertiesCorrect(
            array(
                'identifier' => $roleCreateStruct->identifier
            ),
            $createdRole
        );

        self::assertCount( 2, $createdRole->getPolicies() );

        foreach ( $createdRole->getPolicies() as $policy )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Policy', $policy );
            self::assertGreaterThan( 0, $policy->id );
            self::assertEquals( $createdRole->id, $policy->roleId );

            self::assertCount( 2, $policy->getLimitations() );

            foreach ( $policy->getLimitations() as $limitation )
            {
                self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation', $limitation );

                if ( $policy->module == 'content' && $policy->function == 'read' )
                {
                    switch ( $limitation->getIdentifier() )
                    {
                        case Limitation::CONTENTTYPE:
                            self::assertEquals( $limitation1->limitationValues, $limitation->limitationValues );
                            break;

                        case Limitation::SECTION:
                            self::assertEquals( $limitation2->limitationValues, $limitation->limitationValues );
                            break;

                        default:
                            self::fail( 'Created role contains limitations not defined with create struct' );
                    }
                }
                else if ( $policy->module == 'content' && $policy->function == 'edit' )
                {
                    switch ( $limitation->getIdentifier() )
                    {
                        case Limitation::OWNER:
                            self::assertEquals( $limitation3->limitationValues, $limitation->limitationValues );
                            break;

                        case Limitation::USERGROUP:
                            self::assertEquals( $limitation4->limitationValues, $limitation->limitationValues );
                            break;

                        default:
                            self::fail( 'Created role contains limitations not defined with create struct' );
                    }
                }
                else
                {
                    self::fail( 'Created role contains policy not defined with create struct' );
                }
            }
        }
    }

    /**
     * Test updating role
     * @covers \eZ\Publish\API\Repository\RoleService::updateRole
     */
    public function testUpdateRole()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Anonymous' );

        $roleUpdateStruct = $roleService->newRoleUpdateStruct();
        $roleUpdateStruct->identifier = "Anonymous 2";

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleUpdateStruct->mainLanguageCode = 'eng-US';
        // $roleUpdateStruct->names['eng-US'] = 'Anonymous 2';
        // $roleUpdateStruct->descriptions['eng-US'] = 'Anonymous 2 role';

        $updatedRole = $roleService->updateRole( $role, $roleUpdateStruct );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $updatedRole );

        // @todo: enable
/*
        self::assertEquals(
            array(
                'eng-US' => $roleUpdateStruct->names['eng-US']
            ),
            $updatedRole->getNames()
        );

        self::assertEquals(
            array(
                'eng-US' => $roleUpdateStruct->descriptions['eng-US']
            ),
            $updatedRole->getDescriptions()
        );
*/
        $this->assertPropertiesCorrect(
            array(
                'id' => $role->id,
                'identifier' => $roleUpdateStruct->identifier,
                'policies' => $role->getPolicies()
            ),
            $updatedRole
        );
    }

    /**
     * Test updating role throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::updateRole
     */
    public function testUpdateRoleThrowsInvalidArgumentException()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Anonymous' );

        $roleUpdateStruct = $roleService->newRoleUpdateStruct();
        $roleUpdateStruct->identifier = 'Administrator';

        $roleService->updateRole( $role, $roleUpdateStruct );
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

        $updatedRole = $roleService->addPolicy( $role, $policyCreateStruct );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $updatedRole );
        self::assertCount( $policyCount + 1, $updatedRole->getPolicies() );

        foreach ( $updatedRole->getPolicies() as $policy )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Policy', $policy );
            self::assertGreaterThan( 0, $policy->id );
            self::assertEquals( $role->id, $policy->roleId );
        }
    }

    /**
     * Test removing policy from the role
     * @covers \eZ\Publish\API\Repository\RoleService::removePolicy
     */
    public function testRemovePolicy()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        $policies = $role->getPolicies();
        $policyCount = count( $policies );

        self::assertGreaterThan( 0, $policyCount );

        $updatedRole = $roleService->removePolicy( $role, $policies[0] );
        self::assertCount( $policyCount - 1, $updatedRole->getPolicies() );
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
        $policies = $role->getPolicies();
        $policy = $policies[2];

        $limitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $limitation->limitationValues = array( '12', '13', '14' );

        $policyUpdateStruct = $roleService->newPolicyUpdateStruct();
        $policyUpdateStruct->addLimitation( $limitation );

        $updatedPolicy = $roleService->updatePolicy( $policy, $policyUpdateStruct );

        $this->assertPropertiesCorrect(
            array(
                'id' => $policy->id,
                'roleId' => $policy->roleId,
                'module' => $policy->module,
                'function' => $policy->function
            ),
            $updatedPolicy
        );

        $limitations = $updatedPolicy->getLimitations();

        self::assertCount( 1, $limitations );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation', $limitations[0] );
        self::assertEquals( Limitation::CONTENTTYPE, $limitations[0]->getIdentifier() );
        self::assertEquals( $limitation->limitationValues, $limitations[0]->limitationValues );
    }

    /**
     * Test loading role by id
     * @covers \eZ\Publish\API\Repository\RoleService::loadRole
     */
    public function testLoadRole()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $role );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'identifier' => 'Anonymous'
            ),
            $role
        );
    }

    /**
     * Test loading role by id throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\RoleService::loadRole
     */
    public function testLoadRoleThrowsNotFoundException()
    {
        $roleService = $this->repository->getRoleService();

        $roleService->loadRole( PHP_INT_MAX );
    }

    /**
     * Test loading role by identifier
     * @covers \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier
     */
    public function testLoadRoleByIdentifier()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Anonymous' );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $role );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'identifier' => 'Anonymous'
            ),
            $role
        );
    }

    /**
     * Test loading role by identifier throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier
     */
    public function testLoadRoleByIdentifierThrowsNotFoundException()
    {
        $roleService = $this->repository->getRoleService();

        $roleService->loadRoleByIdentifier( 'non-existing' );
    }

    /**
     * Test loading all roles
     * @covers \eZ\Publish\API\Repository\RoleService::loadRoles
     */
    public function testLoadRoles()
    {
        $roleService = $this->repository->getRoleService();

        $roles = $roleService->loadRoles();
        self::assertInternalType( 'array', $roles );
        self::assertGreaterThan( 0, count( $roles ) );

        foreach ( $roles as $role )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $role );
            self::assertGreaterThan( 0, $role->id );
        }
    }

    /**
     * Test deleting a role
     * @covers \eZ\Publish\API\Repository\RoleService::deleteRole
     */
    public function testDeleteRole()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Role', $role );

        $roleService->deleteRole( $role );

        try
        {
            $roleService->loadRole( 1 );
            self::fail( 'Succeeded loading role after deleting it' );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test loading policies by user ID
     * @covers \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId
     */
    public function testLoadPoliciesByUserId()
    {
        $roleService = $this->repository->getRoleService();

        $policies = $roleService->loadPoliciesByUserId( 10 );
        self::assertInternalType( 'array', $policies );
        self::assertNotEmpty( $policies );

        foreach ( $policies as $policy )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Policy', $policy );
            self::assertGreaterThan( 0, $policy->id );
            self::assertGreaterThan( 0, $policy->roleId );
        }
    }

    /**
     * Test loading policies by non existing user ID
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId
     */
    public function testLoadPoliciesByNonExistingUserId()
    {
        $roleService = $this->repository->getRoleService();

        $roleService->loadPoliciesByUserId( PHP_INT_MAX );
    }

    /**
     * Test assigning role to user group
     * @covers \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup
     */
    public function testAssignRoleToUserGroup()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        $userGroup = $this->repository->getUserService()->loadUserGroup( 12 );

        $originalAssignmentCount = count( $roleService->getRoleAssignmentsForUserGroup( $userGroup ) );

        $roleService->assignRoleToUserGroup( $role, $userGroup );
        $newAssignmentCount = count( $roleService->getRoleAssignmentsForUserGroup( $userGroup ) );
        self::assertEquals( $originalAssignmentCount + 1, $newAssignmentCount );
    }

    /**
     * Test unassigning role from user group
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup
     */
    public function testUnassignRoleFromUserGroup()
    {
        $roleService = $this->repository->getRoleService();

        $anonymousRole = $roleService->loadRole( 1 );
        $anonymousUserGroup = $this->repository->getUserService()->loadUserGroup( 42 );

        $originalAssignmentCount = count( $roleService->getRoleAssignmentsForUserGroup( $anonymousUserGroup ) );

        $roleService->unassignRoleFromUserGroup( $anonymousRole, $anonymousUserGroup );
        $newAssignmentCount = count( $roleService->getRoleAssignmentsForUserGroup( $anonymousUserGroup ) );
        self::assertEquals( $originalAssignmentCount - 1, $newAssignmentCount );
    }

    /**
     * Test unassigning role from user group
     *
     * But on current admin user so he lacks access to read roles.
     *
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnassignRoleFromUserGroupUnauthorizedException()
    {
        $roleService = $this->repository->getRoleService();

        try
        {
            $adminRole = $roleService->loadRole( 2 );
            $adminUserGroup = $this->repository->getUserService()->loadUserGroup( 12 );
            $roleService->getRoleAssignmentsForUserGroup( $adminUserGroup );
            $roleService->unassignRoleFromUserGroup( $adminRole, $adminUserGroup );
        }
        catch ( \Exception $e )
        {
            self::fail( "Unexpected exception: " . $e->getMessage() . " \n[" . $e->getFile() . ' (' . $e->getLine() . ')]' );
        }

        $roleService->getRoleAssignmentsForUserGroup( $adminUserGroup );
    }

    /**
     * Test unassigning role from user group which is not already assigned to the group
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup
     */
    public function testUnassignRoleFromUserGroupThrowsInvalidArgumentException()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 1 );
        $userGroup = $this->repository->getUserService()->loadUserGroup( 12 );

        $roleService->unassignRoleFromUserGroup( $role, $userGroup );
    }

    /**
     * Test assigning role to user
     * @covers \eZ\Publish\API\Repository\RoleService::assignRoleToUser
     */
    public function testAssignRoleToUser()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 2 );
        $user = $this->repository->getUserService()->loadUser( 14 );

        $originalAssignmentCount = count( $roleService->getRoleAssignmentsForUser( $user ) );

        $roleService->assignRoleToUser( $role, $user );
        $newAssignmentCount = count( $roleService->getRoleAssignmentsForUser( $user ) );
        self::assertEquals( $originalAssignmentCount + 1, $newAssignmentCount );
    }

    /**
     * Test unassigning role from user
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser
     */
    public function testUnassignRoleFromUser()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 2 );
        $user = $this->repository->getUserService()->loadUser( 14 );

        $originalAssignmentCount = count( $roleService->getRoleAssignmentsForUser( $user ) );

        $roleService->assignRoleToUser( $role, $user );
        $newAssignmentCount = count( $roleService->getRoleAssignmentsForUser( $user ) );
        self::assertEquals( $originalAssignmentCount + 1, $newAssignmentCount );

        $roleService->unassignRoleFromUser( $role, $user );
        $finalAssignmentCount = count( $roleService->getRoleAssignmentsForUser( $user ) );
        self::assertEquals( $newAssignmentCount - 1, $finalAssignmentCount );
    }

    /**
     * Test unassigning role from user which is not already assigned to the user
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser
     */
    public function testUnassignRoleFromUserThrowsInvalidArgumentException()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 2 );
        $user = $this->repository->getUserService()->loadUser( 14 );

        $roleService->unassignRoleFromUser( $role, $user );
    }

    /**
     * Test fetching all role assignments for specified role
     * @covers \eZ\Publish\API\Repository\RoleService::getRoleAssignments
     */
    public function testGetRoleAssignments()
    {
        $roleService = $this->repository->getRoleService();

        $role = $roleService->loadRole( 2 );

        $roleAssignments = $roleService->getRoleAssignments( $role );

        self::assertInternalType( "array", $roleAssignments );
        self::assertNotEmpty( $roleAssignments );

        foreach ( $roleAssignments as $assignment )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\RoleAssignment', $assignment );
        }
    }

    /**
     * Test fetching role assignments for specified user
     * @covers \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser
     */
    public function testGetRoleAssignmentsForUser()
    {
        $roleService = $this->repository->getRoleService();

        $user = $this->repository->getUserService()->loadUser( 14 );
        $role = $roleService->loadRole( 2 );
        $roleService->assignRoleToUser( $role, $user );

        $userAssignments = $roleService->getRoleAssignmentsForUser( $user );

        self::assertInternalType( "array", $userAssignments );
        self::assertNotEmpty( $userAssignments );

        foreach ( $userAssignments as $assignment )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserRoleAssignment', $assignment );
        }
    }

    /**
     * Test fetching role assignments for specified user group
     * @covers \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup
     */
    public function testGetRoleAssignmentsForUserGroup()
    {
        $userGroup = $this->repository->getUserService()->loadUserGroup( 12 );

        $userGroupAssignments = $this->repository->getRoleService()->getRoleAssignmentsForUserGroup( $userGroup );

        self::assertInternalType( "array", $userGroupAssignments );
        self::assertNotEmpty( $userGroupAssignments );

        foreach ( $userGroupAssignments as $assignment )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment', $assignment );
        }
    }

    /**
     * Test creating new RoleCreateStruct
     * @covers \eZ\Publish\API\Repository\RoleService::newRoleCreateStruct
     */
    public function testNewRoleCreateStruct()
    {
        $roleService = $this->repository->getRoleService();

        $roleCreateStruct = $roleService->newRoleCreateStruct( "Ultimate permissions" );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\RoleCreateStruct', $roleCreateStruct );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => "Ultimate permissions",
                // @todo uncomment when support for multilingual names and descriptions is added
                // 'mainLanguageCode' => null,
                // 'names' => null,
                // 'descriptions' => null
            ),
            $roleCreateStruct
        );

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

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\PolicyCreateStruct', $policyCreateStruct );

        $this->assertPropertiesCorrect(
            array(
                'module' => 'content',
                'function' => 'read'
            ),
            $policyCreateStruct
        );

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

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\RoleUpdateStruct', $roleUpdateStruct );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => null,
                // @todo uncomment when support for multilingual names and descriptions is added
                // 'mainLanguageCode' => null,
                // 'names' => null,
                // 'descriptions' => null
            ),
            $roleUpdateStruct
        );
    }

    /**
     * Test creating new PolicyUpdateStruct
     * @covers \eZ\Publish\API\Repository\RoleService::newPolicyUpdateStruct
     */
    public function testNewPolicyUpdateStruct()
    {
        $roleService = $this->repository->getRoleService();

        $policyUpdateStruct = $roleService->newPolicyUpdateStruct();

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct', $policyUpdateStruct );

        self::assertInternalType( "array", $policyUpdateStruct->getLimitations() );
        self::assertEmpty( $policyUpdateStruct->getLimitations() );
    }
}
