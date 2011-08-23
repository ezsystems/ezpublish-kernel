<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\User\UserHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\User;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\User,
    ezp\Persistence;

/**
 * Test case for UserHandlerTest
 */
class UserHandlerTest extends TestCase
{
    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    protected function getUserHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new User\Handler(
            new User\Gateway\EzcDatabase( $dbHandler ),
            new User\Role\Gateway\EzcDatabase( $dbHandler )
        );
    }

    protected function getValidUser()
    {
        $user = new Persistence\User();
        $user->id = 42;
        $user->login = 'kore';
        $user->email = 'kore@example.org';
        $user->password = '1234567890';
        $user->hashAlgorithm = 'md5';

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

        $handler->delete( 'not_existing' );
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
            array( array( 42, 'kore@example.org', 'new_login', 1234567890, 'md5' ) ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezuser' ),
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
        $role->name = 'Test';

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
        $role->name = 'Test';

        $role = $handler->createRole( $role );

        $this->assertSame( '1', $role->id );
    }

    public function testUpdateRole()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->name = 'Test';

        $role = $handler->createRole( $role );

        $update = new Persistence\User\RoleUpdateStruct();
        $update->id = $role->id;
        $update->name = 'Changed';

        $handler->updateRole( $update );

        $this->assertQueryResult(
            array( array( 1, 'Changed' ) ),
            $this->handler->createSelectQuery()->select( 'id', 'name' )->from( 'ezrole' ),
            'Expected a changed role.'
        );
    }

    public function testDeleteRole()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->name = 'Test';

        $role = $handler->createRole( $role );

        $handler->deleteRole( $role->id );

        $this->assertQueryResult(
            array( ),
            $this->handler->createSelectQuery()->select( 'id', 'name' )->from( 'ezrole' ),
            'Expected an empty set.'
        );
    }

    public function testAddPolicyToRole()
    {
        $handler = $this->getUserHandler();

        $role = new Persistence\User\Role();
        $role->name = 'Test';
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
        $role->name = 'Test';
        $handler->createRole( $role );

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $policy = $handler->addPolicy( $role->id, $policy );

        $this->assertEquals( 1, $policy->id );
    }

    protected function createRole()
    {
        $handler = $this->getUserHandler();

        $policy1 = new Persistence\User\Policy();
        $policy1->module = 'foo';
        $policy1->function = 'bar';

        $policy2 = new Persistence\User\Policy();
        $policy2->module = 'foo';
        $policy2->function = 'blubb';

        $role = new Persistence\User\Role();
        $role->name = 'Test';
        $role->policies = array( $policy1, $policy2 );
        return $handler->createRole( $role );
    }

    public function testImplicitelyCreatePolicies()
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

        $handler->assignRole( $user->id, $role->id, array(
            'Subtree' => array( '/1' ),
        ) );

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

        $handler->assignRole( $user->id, $role->id, array(
            'Subtree' => array( '/1', '/1/2' ),
            'Foo' => array( 'Bar' ),
        ) );

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

        $handler->assignRole( $user->id, $role->id, array(
            'Subtree' => array( '/1', '/1/2' ),
            'Foo' => array( 'Bar' ),
        ) );

        $handler->unAssignRole( $user->id, $role->id );

        $this->assertQueryResult(
            array(),
            $this->handler->createSelectQuery()->select( 'id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value' )->from( 'ezuser_role' ),
            'Expected no user policy associations.'
        );
    }
}
