<?php
/**
 * File containing the RoleServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;

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
 * @group role
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
        $roleCreate = $roleService->newRoleCreateStruct( 'roleName' );

        $this->assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleCreateStruct', $roleCreate );
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
        $roleCreate = $roleService->newRoleCreateStruct( 'roleName' );

        /* END: Use Case */

        $this->assertEquals( 'roleName', $roleCreate->identifier );
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
        $roleCreate = $roleService->newRoleCreateStruct( 'roleName' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole( $roleCreate );

        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Role',
            $role
        );
    }

    /**
     * Test for the createRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testCreateRoleThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct( 'Editor' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        // This call will fail with an InvalidArgumentException, because Editor exists
        $roleService->createRole( $roleCreate );

        /* END: Use Case */
    }

    /**
     * Test for the createRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleCreateStruct
     */
    public function testCreateRoleInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        $repository->beginTransaction();

        $roleCreate = $roleService->newRoleCreateStruct( 'roleName' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $createdRoleId = $roleService->createRole( $roleCreate )->id;

        $repository->rollback();

        try
        {
            // This call will fail with a "NotFoundException"
            $role = $roleService->loadRole( $createdRoleId );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            return;
        }
        /* END: Use Case */

        $this->fail( 'Role object still exists after rollback.' );
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
        $roleCreate = $roleService->newRoleCreateStruct( 'roleName' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleId = $roleService->createRole( $roleCreate )->id;

        // Load the newly create role by it's name
        $role = $roleService->loadRole( $roleId );

        /* END: Use Case */

        $this->assertEquals( 'roleName', $role->identifier );
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

        $nonExistingRoleId = $this->generateId( 'role', PHP_INT_MAX );
        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        // This call will fail with a NotFoundException, because no such role exists.
        $roleService->loadRole( $nonExistingRoleId );

        /* END: Use Case */
    }

    /**
     * Test for the loadRoleByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testLoadRoleByIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct( 'roleName' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleService->createRole( $roleCreate );

        // Load the newly create role by it's name
        $role = $roleService->loadRoleByIdentifier( 'roleName' );

        /* END: Use Case */

        $this->assertEquals( 'roleName', $role->identifier );
    }

    /**
     * Test for the loadRoleByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testLoadRoleByIdentifierThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $roleService = $repository->getRoleService();

        // This call will fail with a NotFoundException, because no such role exists.
        $roleService->loadRoleByIdentifier( 'MissingRole' );

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
        $roleCreate = $roleService->newRoleCreateStruct( 'roleName' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole( $roleCreate );

        // Now load all available roles
        $roles = $roleService->loadRoles();

        foreach ( $roles as $role )
        {
            if ( $role->identifier === 'roleName' )
            {
                break;
            }
        }

        /* END: Use Case */

        $this->assertEquals( 'roleName', $role->identifier );
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
            $roleNames[] = $role->identifier;
        }
        /* END: Use Case */

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
        $roleUpdate = $roleService->newRoleUpdateStruct( 'newRole' );
        /* END: Use Case */

        $this->assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleUpdateStruct', $roleUpdate );
    }

    /**
     * Test for the updateRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updateRole()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewRoleUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testUpdateRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole( $roleCreate );

        $roleUpdate = $roleService->newRoleUpdateStruct();
        $roleUpdate->identifier = 'updatedRole';

        $updatedRole = $roleService->updateRole( $role, $roleUpdate );
        /* END: Use Case */

        // Now verify that our change was saved
        $role = $roleService->loadRoleByIdentifier( 'updatedRole' );

        $this->assertEquals( $role->id, $updatedRole->id );
    }

    /**
     * Test for the updateRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updateRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdateRole
     */
    public function testUpdateRoleThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();
        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole( $roleCreate );

        $roleUpdate = $roleService->newRoleUpdateStruct();
        $roleUpdate->identifier = 'Editor';

        // This call will fail with an InvalidArgumentException, because Editor is a predefined role
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
        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole( $roleCreate );

        $roleService->deleteRole( $role );
        /* END: Use Case */

        $this->assertEquals( 5, count( $roleService->loadRoles() ) );
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
        $roleService = $repository->getRoleService();
        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        /* END: Use Case */

        $this->assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct', $policyCreate );
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
        $roleService = $repository->getRoleService();
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
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyCreateStruct
     */
    public function testAddPolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole( $roleCreate );

        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct( 'content', 'delete' )
        );
        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct( 'content', 'create' )
        );
        /* END: Use Case */

        $actual = array();
        foreach ( $role->getPolicies() as $policy )
        {
            $actual[] = array(
                'module' => $policy->module,
                'function' => $policy->function
            );
        }
        usort(
            $actual,
            function ( $p1, $p2 )
            {
                return strcasecmp( $p1['function'], $p2['function'] );
            }
        );

        $this->assertEquals(
            array(
                array(
                    'module' => 'content',
                    'function' => 'create',
                ),
                array(
                    'module' => 'content',
                    'function' => 'delete',
                )
            ),
            $actual
        );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicy
     */
    public function testAddPolicyUpdatesRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $role = $roleService->createRole( $roleCreate );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $role = $roleService->addPolicy( $role, $policyCreate );

        $policy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( $policy->module === 'content' && $policy->function === 'create' )
            {
                break;
            }
        }
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Policy',
            $policy
        );

        return array( $role, $policy );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @param array $roleAndPolicy
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicyUpdatesRole
     */
    public function testAddPolicySetsPolicyProperties( $roleAndPolicy )
    {
        list( $role, $policy ) = $roleAndPolicy;

        $this->assertEquals(
            array( $role->id, 'content', 'create' ),
            array( $policy->roleId, $policy->module, $policy->function )
        );
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

        // Instantiate a new create struct
        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Add some role policies
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct(
                'content',
                'read'
            )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct(
                'content',
                'translate'
            )
        );

        // Create new role instance
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
                    'module' => 'content',
                    'function' => 'read'
                ),
                array(
                    'module' => 'content',
                    'function' => 'translate'
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
        $roleService = $repository->getRoleService();
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyUpdateStruct',
            $policyUpdate
        );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @return array
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicy
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testNewPolicyUpdateStruct
     */
    public function testUpdatePolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate new policy create
        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'translate' );

        // Add some limitations for the new policy
        $policyCreate->addLimitation(
            new LanguageLimitation(
                array(
                    'limitationValues' => array( 28, 29 )
                )
            )
        );

        // Instantiate a role create and add the policy create
        $roleCreate = $roleService->newRoleCreateStruct( 'myRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy( $policyCreate );

        // Create a new role instance.
        $role = $roleService->createRole( $roleCreate );

        // Search for the new policy instance
        $policy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( $policy->module === 'content' && $policy->function === 'translate' )
            {
                break;
            }
        }

        // Create an update struct and set a modified limitation
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                array(
                    'limitationValues' => array( 29, 30 )
                )
            )
        );

        // Update the the policy
        $policy = $roleService->updatePolicy( $policy, $policyUpdate );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Policy',
            $policy
        );

        return array( $roleService->loadRole( $role->id ), $policy );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @param array $roleAndPolicy
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdatePolicy
     */
    public function testUpdatePolicyUpdatesLimitations( $roleAndPolicy )
    {
        list( $role, $policy ) = $roleAndPolicy;

        $this->assertEquals(
            array(
                new ContentTypeLimitation(
                    array(
                        'limitationValues' => array( 29, 30 )
                    )
                )
            ),
            $policy->getLimitations()
        );

        return $role;
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdatePolicyUpdatesLimitations
     */
    public function testUpdatePolicyUpdatesRole( $role )
    {
        $limitations = array();
        foreach ( $role->getPolicies() as $policy )
        {
            foreach ( $policy->getLimitations() as $limitation )
            {
                $limitations[] = $limitation;
            }
        }

        $this->assertEquals(
            array(
                new ContentTypeLimitation(
                    array(
                        'limitationValues' => array( 29, 30 )
                    )
                )
            ),
            $limitations
        );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::removePolicy()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAddPolicy
     */
    public function testRemovePolicy()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Instantiate a new role create
        $roleCreate = $roleService->newRoleCreateStruct( 'newRole' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        // Create a new role with two policies
        $role = $roleService->createRole(
            $roleCreate,
            array(
                $roleService->newPolicyCreateStruct( 'content', 'create' ),
                $roleService->newPolicyCreateStruct( 'content', 'delete' ),
            )
        );

        // Delete all policies from the new role
        foreach ( $role->getPolicies() as $policy )
        {
            $role = $roleService->removePolicy( $role, $policy );
        }
        /* END: Use Case */

        $this->assertSame( array(), $role->getPolicies() );
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testGetRoleAssignments()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // Load the editor role
        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        // Load all assigned users and user groups
        $roleAssignments = $roleService->getRoleAssignments( $role );

        /* END: Use Case */

        $this->assertEquals( 1, count( $roleAssignments ) );
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
            reset( $roleAssignments )
        );

        return $roleAssignments;
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleAssignment[] $roleAssignments
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignments
     */
    public function testGetRoleAssignmentsContainExpectedLimitation( array $roleAssignments )
    {
        $this->assertEquals(
            'Subtree',
            reset( $roleAssignments )->limitation->getIdentifier()
        );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignments
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testAssignRoleToUser()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the existing "Administrator" role
        $role = $roleService->loadRoleByIdentifier( 'Administrator' );

        // Assign the "Administrator" role to the newly created user
        $roleService->assignRoleToUser( $role, $user );

        // The assignments array will contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments( $role );
        /* END: Use Case */

        // Administrator + Example User
        $this->assertEquals( 2, count( $roleAssignments ) );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     */
    public function testAssignRoleToUserWithRoleLimitation()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier( 'Anonymous' );

        // Assign the "Anonymous" role to the newly created user
        $roleService->assignRoleToUser(
            $role,
            $user,
            new SubtreeLimitation(
                array(
                    'limitationValues' => array( '/1/43/' )
                )
            )
        );

        // The assignments array will contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments( $role );
        /* END: Use Case */

        // Members + Partners + Anonymous + Example User
        $this->assertEquals( 4, count( $roleAssignments ) );

        // Get the role limitation
        $roleLimitation = null;
        foreach ( $roleAssignments as $roleAssignment )
        {
            $roleLimitation = $roleAssignment->getRoleLimitation();
            if ( $roleLimitation )
            {
                break;
            }
        }

        $this->assertEquals(
            new SubtreeLimitation(
                array(
                    'limitationValues' => array( '/1/43/' )
                )
            ),
            $roleLimitation
        );
    }

    /**
     * Test for the unassignRoleFromUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     */
    public function testUnassignRoleFromUser()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the existing "Member" role
        $role = $roleService->loadRoleByIdentifier( 'Member' );

        // Assign the "Member" role to the newly created user
        $roleService->assignRoleToUser( $role, $user );

        // Unassign user from role
        $roleService->unassignRoleFromUser( $role, $user );

        // The assignments array will not contain the new role<->user assignment
        $roleAssignments = $roleService->getRoleAssignments( $role );
        /* END: Use Case */

        // Members + Editors + Partners
        $this->assertEquals( 3, count( $roleAssignments ) );
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
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the existing "Member" role
        $role = $roleService->loadRoleByIdentifier( 'Member' );

        // This call will fail with a "InvalidArgumentException", because the
        // user does not have the "Member" role.
        $roleService->unassignRoleFromUser( $role, $user );
        /* END: Use Case */
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleWithAddPolicy
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testGetRoleAssignmentsForUser()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Instantiate a role create and add some policies
        $roleCreate = $roleService->newRoleCreateStruct( 'Example Role' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'user', 'login' )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'content', 'read' )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'content', 'edit' )
        );

        // Create the new role instance
        $role = $roleService->createRole( $roleCreate );

        // Assign role to new user
        $roleService->assignRoleToUser( $role, $user );

        // Load the currently assigned role
        $roleAssignments = $roleService->getRoleAssignmentsForUser( $user );
        /* END: Use Case */

        $this->assertEquals( 1, count( $roleAssignments ) );
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserRoleAssignment',
            reset( $roleAssignments )
        );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testGetRoleAssignments
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testAssignRoleToUserGroup()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the existing "Administrator" role
        $role = $roleService->loadRoleByIdentifier( 'Administrator' );

        // Assign the "Administrator" role to the newly created user group
        $roleService->assignRoleToUserGroup( $role, $userGroup );

        // The assignments array will contain the new role<->group assignment
        $roleAssignments = $roleService->getRoleAssignments( $role );
        /* END: Use Case */

        // Administrator + Example Group
        $this->assertEquals( 2, count( $roleAssignments ) );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testAssignRoleToUserGroupWithRoleLimitation()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the existing "Anonymous" role
        $role = $roleService->loadRoleByIdentifier( 'Anonymous' );

        // Assign the "Anonymous" role to the newly created user group
        $roleService->assignRoleToUserGroup(
            $role,
            $userGroup,
            new SubtreeLimitation(
                array(
                    'limitationValues' => array( '/1/43/' )
                )
            )
        );

        // The assignments array will contain the new role<->group assignment
        $roleAssignments = $roleService->getRoleAssignments( $role );
        /* END: Use Case */

        // Members + Partners + Anonymous + Example Group
        $this->assertEquals( 4, count( $roleAssignments ) );

        // Get the role limitation
        $roleLimitation = null;
        foreach ( $roleAssignments as $roleAssignment )
        {
            $roleLimitation = $roleAssignment->getRoleLimitation();
            if ( $roleLimitation )
            {
                break;
            }
        }

        $this->assertEquals(
            new SubtreeLimitation(
                array(
                    'limitationValues' => array( '/1/43/' )
                )
            ),
            $roleLimitation
        );
    }

    /**
     * Test for the unassignRoleFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testUnassignRoleFromUserGroup()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the existing "Member" role
        $role = $roleService->loadRoleByIdentifier( 'Member' );

        // Assign the "Member" role to the newly created user group
        $roleService->assignRoleToUserGroup( $role, $userGroup );

        // Unassign group from role
        $roleService->unassignRoleFromUserGroup( $role, $userGroup );

        // The assignments array will not contain the new role<->group assignment
        $roleAssignments = $roleService->getRoleAssignments( $role );
        /* END: Use Case */

        // Members + Editors + Partners
        $this->assertEquals( 3, count( $roleAssignments ) );
    }

    /**
     * Test for the unassignRoleFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUnassignRoleFromUserGroup
     */
    public function testUnassignRoleFromUserGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the existing "Member" role
        $role = $roleService->loadRoleByIdentifier( 'Member' );

        // This call will fail with a "InvalidArgumentException", because the
        // user group does not have the "Member" role.
        $roleService->unassignRoleFromUserGroup( $role, $userGroup );
        /* END: Use Case */
    }

    /**
     * Test for the getRoleAssignmentsForUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRoleWithAddPolicy
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testGetRoleAssignmentsForUserGroup()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Instantiate a role create and add some policies
        $roleCreate = $roleService->newRoleCreateStruct( 'Example Role' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'user', 'login' )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'content', 'read' )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'content', 'edit' )
        );

        // Create the new role instance
        $role = $roleService->createRole( $roleCreate );

        // Assign role to new user group
        $roleService->assignRoleToUserGroup( $role, $userGroup );

        // Load the currently assigned role
        $roleAssignments = $roleService->getRoleAssignmentsForUserGroup( $userGroup );
        /* END: Use Case */

        $this->assertEquals( 1, count( $roleAssignments ) );
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupRoleAssignment',
            reset( $roleAssignments )
        );
    }

    /**
     * Test for the loadPoliciesByUserId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId()
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUser
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testAssignRoleToUserGroup
     */
    public function testLoadPoliciesByUserId()
    {
        $repository = $this->getRepository();

        $anonUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonUserId is the ID of the "Anonymous" user.

        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();

        // Load "Anonymous" user
        $user = $userService->loadUser( $anonUserId );

        // Instantiate a role create and add some policies
        $roleCreate = $roleService->newRoleCreateStruct( 'User Role' );

        // @todo uncomment when support for multilingual names and descriptions is added
        // $roleCreate->mainLanguageCode = 'eng-US';

        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'notification', 'use' )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'user', 'password' )
        );
        $roleCreate->addPolicy(
            $roleService->newPolicyCreateStruct( 'user', 'selfedit' )
        );

        // Create the new role instance
        $role = $roleService->createRole( $roleCreate );

        // Assign role to anon user
        $roleService->assignRoleToUser( $role, $user );

        // Load the currently assigned role
        $policies = array();
        foreach ( $roleService->loadPoliciesByUserId( $user->id ) as $policy )
        {
            $policies[] = array( $policy->roleId, $policy->module, $policy->function );
        }
        /* END: Use Case */
        array_multisort( $policies );

        $this->assertEquals(
            array(
                array( 1, 'content', 'pdf' ),
                array( 1, 'content', 'read' ),
                array( 1, 'content', 'read' ),
                array( 1, 'rss', 'feed' ),
                array( 1, 'user', 'login' ),
                array( 1, 'user', 'login' ),
                array( 1, 'user', 'login' ),
                array( 1, 'user', 'login' ),
                array( $role->id, 'notification', 'use' ),
                array( $role->id, 'user', 'password' ),
                array( $role->id, 'user', 'selfedit' ),
            ),
            $policies
        );
    }

    /**
     * Test for the loadPoliciesByUserId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadPoliciesByUserId
     */
    public function testLoadPoliciesByUserIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingUserId = $this->generateId( 'user', PHP_INT_MAX );
        /* BEGIN: Use Case */
        $roleService = $repository->getRoleService();

        // This call will fail with a "NotFoundException", because hopefully no
        // user with an ID equal to PHP_INT_MAX exists.
        $roleService->loadPoliciesByUserId( $nonExistingUserId );
        /* END: Use Case */
    }

    /**
     * Create a user group fixture in a variable named <b>$userGroup</b>,
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private function createUserGroupVersion1()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Inline */
        // $mainGroupId is the ID of the main "Users" group

        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        $userGroupCreate->setField( 'name', 'Example Group' );

        // Create the new user group
        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $parentUserGroup
        );
        /* END: Inline */

        return $userGroup;
    }
}
