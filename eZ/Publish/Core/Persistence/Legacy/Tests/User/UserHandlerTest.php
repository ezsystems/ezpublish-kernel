<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\User\UserHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\User;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\User;
use eZ\Publish\SPI\Persistence;

/**
 * Test case for UserHandlerTest
 */
class UserHandlerTest extends TestCase
{
    protected function getUserHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new User\Handler(
            new User\Gateway\EzcDatabase( $dbHandler ),
            new User\Role\Gateway\EzcDatabase( $dbHandler ),
            new User\Mapper()
        );
    }

    protected function getValidUser()
    {
        $user = new Persistence\User();
        $user->id = 42;
        $user->login = 'kore';
        $user->email = 'kore@example.org';
        $user->passwordHash = '1234567890';
        $user->hashAlgorithm = 2;
        $user->isEnabled = true;
        $user->maxLogin = 23;

        return $user;
    }

    public function testCreateUser()
    {
        $handler = $this->getUserHandler();

        $handler->create( $this->getValidUser() );
        $this->assertQueryResult(
            array( array( 1 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected one user to be created.'
        );

        $this->assertQueryResult(
            array( array( 1 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser_setting' ),
            'Expected one user setting to be created.'
        );
    }

    /**
     * @expectedException \PDOException
     */
    public function testCreateDuplicateUser()
    {
        $handler = $this->getUserHandler();

        $handler->create( $user = $this->getValidUser() );
        $handler->create( $user );
    }

    /**
     * @expectedException \PDOException
     */
    public function testInsertIncompleteUser()
    {
        $handler = $this->getUserHandler();

        $user = new Persistence\User();
        $user->id = 42;

        $handler->create( $user );
    }

    public function testLoadUser()
    {
        $handler = $this->getUserHandler();
        $handler->create( $user = $this->getValidUser() );

        $this->assertEquals(
            $user,
            $handler->load( $user->id )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadUnknownUser()
    {
        $handler = $this->getUserHandler();

        $handler->load( 1337 );
    }

    public function testLoadUserByLogin()
    {
        $handler = $this->getUserHandler();
        $handler->create( $user = $this->getValidUser() );

        $users = $handler->loadByLogin( $user->login );
        $this->assertEquals(
            $user,
            $users[0]
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadUserByEmailNotFound()
    {
        $handler = $this->getUserHandler();
        $handler->create( $user = $this->getValidUser() );

        $handler->loadByLogin( $user->email );
    }

    public function testLoadUserByEmail()
    {
        $handler = $this->getUserHandler();
        $handler->create( $user = $this->getValidUser() );

        $users = $handler->loadByLogin( $user->email, true );
        $this->assertEquals(
            $user,
            $users[0]
        );
    }

    public function testCreateAndDeleteUser()
    {
        $handler = $this->getUserHandler();

        $handler->create( $user = $this->getValidUser() );
        $this->assertQueryResult(
            array( array( 1 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected one user to be created.'
        );

        $handler->delete( $user->id );
        $this->assertQueryResult(
            array( array( 0 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected one user to be removed.'
        );
    }

    public function testDeleteNonExistingUser()
    {
        $handler = $this->getUserHandler();

        $handler->delete( 1337 );
        $this->assertQueryResult(
            array( array( 0 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected no existing user.'
        );
    }

    public function testUpdateUser()
    {
        $handler = $this->getUserHandler();

        $handler->create( $user = $this->getValidUser() );

        $user->login = 'new_login';
        $handler->update( $user );

        $this->assertQueryResult(
            array( array( 42, 'kore@example.org', 'new_login', 1234567890, '2' ) ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezuser' ),
            'Expected user data to be updated.'
        );
    }

    public function testUpdateUserSettings()
    {
        $handler = $this->getUserHandler();

        $handler->create( $user = $this->getValidUser() );

        $user->maxLogin = 42;
        $handler->update( $user );

        $this->assertQueryResult(
            array( array( 1, 42, 42 ) ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezuser_setting' ),
            'Expected user data to be updated.'
        );
    }

    public function testSilentlyUpdateNotExistingUser()
    {
        $handler = $this->getUserHandler();
        $handler->update( $this->getValidUser() );
        $this->assertQueryResult(
            array( array( 0 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected no existing user.'
        );
    }

    public function testCreateNewRoleWithoutPolicies()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $handler->createRole( $role );

        $this->assertQueryResult(
            array( array( 1, 'Test' ) ),
            $this->handler->createSelectQuery()->select( 'id', 'name' )->from( 'ezrole' ),
            'Expected a new role.'
        );
    }

    public function testCreateNewRoleRoleId()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $this->assertSame( '1', $role->id );
    }

    public function testLoadRole()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $this->assertEquals(
            $role,
            $handler->loadRole( $role->id )
        );
    }

    public function testLoadRoleWithGroups()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $handler->assignRole( 23, $role->id );
        $handler->assignRole( 42, $role->id );

        $loaded = $handler->loadRole( $role->id );
        $this->assertEquals(
            array( 23, 42 ),
            $loaded->groupIds
        );
    }

    public function testLoadRoleWithPolicies()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicy( $role->id, $policy );

        $loaded = $handler->loadRole( $role->id );
        $this->assertEquals(
            array(
                new Persistence\User\Policy(
                    array(
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => '*',
                    )
                )
            ),
            $loaded->policies
        );
    }

    public function testLoadRoleWithPoliciesAndGroups()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicy( $role->id, $policy );

        $handler->assignRole( 23, $role->id );
        $handler->assignRole( 42, $role->id );

        $loaded = $handler->loadRole( $role->id );
        $this->assertEquals(
            array(
                new Persistence\User\Policy(
                    array(
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => '*',
                    )
                )
            ),
            $loaded->policies
        );

        $this->assertEquals(
            array( 23, 42 ),
            $loaded->groupIds
        );
    }

    public function testLoadRoleWithPoliciyLimitations()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = array(
            'Subtree' => array( '/1', '/1/2' ),
            'Foo' => array( 'Bar' ),
        );

        $handler->addPolicy( $role->id, $policy );

        $loaded = $handler->loadRole( $role->id );
        $this->assertEquals(
            array(
                new Persistence\User\Policy(
                    array(
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => array(
                            'Subtree' => array( '/1', '/1/2' ),
                            'Foo' => array( 'Bar' ),
                        ),
                    )
                )
            ),
            $loaded->policies
        );
    }

    public function testLoadRoles()
    {
        $handler = $this->getUserHandler();

        $this->assertEquals(
            array(),
            $handler->loadRoles()
        );

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $this->assertEquals(
            array( $role ),
            $handler->loadRoles()
        );
    }

    public function testUpdateRole()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';

        $role = $handler->createRole( $role );

        $update = new Persistence\User\RoleUpdateStruct();
        $update->id = $role->id;
        $update->identifier = 'Changed';

        $handler->updateRole( $update );

        $this->assertQueryResult(
            array( array( 1, 'Changed' ) ),
            $this->handler->createSelectQuery()->select( 'id', 'name' )->from( 'ezrole' ),
            'Expected a changed role.'
        );
    }

    public function testDeleteRole()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );
        $handler = $this->getUserHandler();

        // 3 is the ID of Editor role
        $handler->deleteRole( 3 );

        $this->assertQueryResult(
            array(),
            $this->handler->createSelectQuery()->select( "id" )->from( "ezrole" )->where( "id = 3" ),
            'Expected an empty set.'
        );

        $this->assertQueryResult(
            array(),
            $this->handler->createSelectQuery()->select( "role_id" )->from( "ezpolicy" )->where( "role_id = 3" ),
            'Expected an empty set.'
        );

        $this->assertQueryResult(
            array(),
            $this->handler->createSelectQuery()->select( "role_id" )->from( "ezuser_role" )->where( "role_id = 3" ),
            'Expected an empty set.'
        );
    }

    public function testAddPolicyToRoleLimitations()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';
        $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicy( $role->id, $policy );

        $this->assertQueryResult(
            array( array( 1, 'foo', 'bar', 1 ) ),
            $this->handler->createSelectQuery()->select( 'id', 'module_name', 'function_name', 'role_id' )->from( 'ezpolicy' ),
            'Expected a new policy.'
        );
    }

    public function testAddPolicyPolicyId()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';
        $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $policy = $handler->addPolicy( $role->id, $policy );

        $this->assertEquals( 1, $policy->id );
    }

    public function testAddPolicyLimitations()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';
        $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = array(
            'Subtree' => array( '/1', '/1/2' ),
            'Foo' => array( 'Bar' ),
        );

        $handler->addPolicy( $role->id, $policy );

        $this->assertQueryResult(
            array(
                array( 1, 'Subtree', 1 ),
                array( 2, 'Foo', 1 ),
            ),
            $this->handler->createSelectQuery()->select( 'id', 'identifier', 'policy_id' )->from( 'ezpolicy_limitation' ),
            'Expected a new policy.'
        );
    }

    public function testAddPolicyLimitationValues()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';
        $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = array(
            'Subtree' => array( '/1', '/1/2' ),
            'Foo' => array( 'Bar' ),
        );

        $handler->addPolicy( $role->id, $policy );

        $this->assertQueryResult(
            array(
                array( 1, '/1', 1 ),
                array( 2, '/1/2', 1 ),
                array( 3, 'Bar', 2 ),
            ),
            $this->handler->createSelectQuery()->select( 'id', 'value', 'limitation_id' )->from( 'ezpolicy_limitation_value' ),
            'Expected a new policy.'
        );
    }

    protected function createRole()
    {
        $handler = $this->getUserHandler();

        $policy1 = new Persistence\User\Policy();
        $policy1->module = 'foo';
        $policy1->function = 'bar';
        $policy1->limitations = array(
            'Subtree' => array( '/1', '/1/2' ),
            'Foo' => array( 'Bar' ),
        );

        $policy2 = new Persistence\User\Policy();
        $policy2->module = 'foo';
        $policy2->function = 'blubb';
        $policy2->limitations = array(
            'Foo' => array( 'Blubb' ),
        );

        $role = new Persistence\User\Role();
        $role->identifier = 'Test';
        $role->policies = array( $policy1, $policy2 );
        return $handler->createRole( $role );
    }

    public function testImplicitlyCreatePolicies()
    {
        $this->createRole();

        $this->assertQueryResult(
            array(
                array( 1, 'foo', 'bar', 1 ),
                array( 2, 'foo', 'blubb', 1 ),
            ),
            $this->handler->createSelectQuery()->select( 'id', 'module_name', 'function_name', 'role_id' )->from( 'ezpolicy' ),
            'Expected a new policy.'
        );
    }

    public function testRemovePolicy()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();
        $handler->removePolicy( $role->id, $role->policies[0]->id );

        $this->assertQueryResult(
            array(
                array( 2, 'foo', 'blubb', 1 ),
            ),
            $this->handler->createSelectQuery()->select( 'id', 'module_name', 'function_name', 'role_id' )->from( 'ezpolicy' ),
            'Expected a new policy.'
        );
    }

    public function testRemovePolicyLimitations()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();
        $handler->removePolicy( $role->id, $role->policies[0]->id );

        $this->assertQueryResult(
            array( array( 3, 'Foo', 2 ) ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezpolicy_limitation' )
        );
    }

    public function testRemovePolicyLimitationValues()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();
        $handler->removePolicy( $role->id, $role->policies[0]->id );

        $this->assertQueryResult(
            array( array( 4, 3, 'Blubb' ) ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezpolicy_limitation_value' )
        );
    }

    public function testUpdatePolicies()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();

        $policy = $role->policies[0];
        $policy->limitations = array(
            'new' => array( 'something' ),
        );

        $handler->updatePolicy( $policy );

        $this->assertQueryResult(
            array(
                array( 3, 'Foo', 2 ),
                array( 4, 'new', 1 ),
            ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezpolicy_limitation' )
        );

        $this->assertQueryResult(
            array(
                array( 4, 3, 'Blubb' ),
                array( 5, 4, 'something' ),
            ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezpolicy_limitation_value' )
        );
    }

    public function testAddRoleToUser()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();
        $handler->create( $user = $this->getValidUser() );

        $handler->assignRole( $user->id, $role->id, array() );

        $this->assertQueryResult(
            array(
                array( 1, 42, 1, null, null ),
            ),
            $this->handler->createSelectQuery()->select( 'id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value' )->from( 'ezuser_role' ),
            'Expected a new user policy association.'
        );
    }

    public function testAddRoleToUserWithLimitation()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();
        $handler->create( $user = $this->getValidUser() );

        $handler->assignRole(
            $user->id,
            $role->id,
            array(
                'Subtree' => array( '/1' ),
            )
        );

        $this->assertQueryResult(
            array(
                array( 1, 42, 1, 'Subtree', '/1' ),
            ),
            $this->handler->createSelectQuery()->select( 'id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value' )->from( 'ezuser_role' ),
            'Expected a new user policy association.'
        );
    }

    public function testAddRoleToUserWithComplexLimitation()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();
        $handler->create( $user = $this->getValidUser() );

        $handler->assignRole(
            $user->id,
            $role->id,
            array(
                'Subtree' => array( '/1', '/1/2' ),
                'Foo' => array( 'Bar' ),
            )
        );

        $this->assertQueryResult(
            array(
                array( 1, 42, 1, 'Subtree', '/1' ),
                array( 2, 42, 1, 'Subtree', '/1/2' ),
                array( 3, 42, 1, 'Foo', 'Bar' ),
            ),
            $this->handler->createSelectQuery()->select( 'id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value' )->from( 'ezuser_role' ),
            'Expected a new user policy association.'
        );
    }

    public function testRemoveUserRoleAssociation()
    {
        $handler = $this->getUserHandler();

        $role = $this->createRole();
        $handler->create( $user = $this->getValidUser() );

        $handler->assignRole(
            $user->id,
            $role->id,
            array(
                'Subtree' => array( '/1', '/1/2' ),
                'Foo' => array( 'Bar' ),
            )
        );

        $handler->unAssignRole( $user->id, $role->id );

        $this->assertQueryResult(
            array(),
            $this->handler->createSelectQuery()->select( 'id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value' )->from( 'ezuser_role' ),
            'Expected no user policy associations.'
        );
    }

    public function testLoadPoliciesForUser()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );
        $handler = $this->getUserHandler();

        $policies = $handler->loadPoliciesByUserId( 10 ); // Anonymous user

        // Verify, that we received an array of Policy objects
        $this->assertTrue(
            array_reduce(
                array_map(
                    function ( $policy )
                    {
                        return $policy instanceof Persistence\User\Policy;
                    },
                    $policies
                ),
                function ( $a, $b )
                {
                    return $a && $b;
                },
                true
            )
        );
        $this->assertEquals( 8, count( $policies ) );
    }

    public function testLoadRoleAssignmentsByGroupId()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );
        $handler = $this->getUserHandler();

        $this->assertEquals(
            array(
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 1,
                        'contentId' => 11
                    )
                ),
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 5,
                        'contentId' => 11
                    )
                )
            ),
            $handler->loadRoleAssignmentsByGroupId( 11 )// 11: Members
        );

        $this->assertEquals(
            array(
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 1,
                        'contentId' => 42
                    )
                )
            ),
            $handler->loadRoleAssignmentsByGroupId( 42 )// 42: Anonymous Users
        );

        $this->assertEquals(
            array(),
            $handler->loadRoleAssignmentsByGroupId( 10 )// 10: Anonymous User
        );
    }

    public function testLoadRoleAssignmentsByGroupIdInherited()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );
        $handler = $this->getUserHandler();

        $this->assertEquals(
            array(
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 1,
                        'contentId' => 42
                    )
                )
            ),
            $handler->loadRoleAssignmentsByGroupId( 10, true )// 10: Anonymous User
        );
    }

    public function testLoadComplexRoleAssignments()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );
        $handler = $this->getUserHandler();

        $this->assertEquals(
            array(
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => array( '/1/2/', '/1/43/' )
                    )
                ),
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 5,
                        'contentId' => 13
                    )
                )
            ),
            $handler->loadRoleAssignmentsByGroupId( 13 )
        );

        $this->assertEquals(
            array(
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => array( '/1/2/', '/1/43/' )
                    )
                ),
                new Persistence\User\RoleAssignment(
                    array(
                        'roleId' => 5,
                        'contentId' => 13
                    )
                )
            ),
            $handler->loadRoleAssignmentsByGroupId( 13, true )
        );
    }
}
