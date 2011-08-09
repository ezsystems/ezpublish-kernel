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

        $handler->createUser( $this->getValidUser() );
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

        $handler->createUser( $user = $this->getValidUser() );
        $handler->createUser( $user );
    }

    /**
     * @expectedException \PDOException
     */
    public function testInsertIncompleteUser()
    {
        $handler = $this->getUserHandler();

        $user = new Persistence\User();
        $user->id = 42;

        $handler->createUser( $user );
    }

    public function testCreateAndDeleteUser()
    {
        $handler = $this->getUserHandler();

        $handler->createUser( $user = $this->getValidUser() );
        $this->assertQueryResult(
            array( array( 1 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected one user to be created.'
        );

        $handler->deleteUser( $user->id );
        $this->assertQueryResult(
            array( array( 0 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected one user to be removed.'
        );
    }

    public function testDeleteNonExistingUser()
    {
        $handler = $this->getUserHandler();

        $handler->deleteUser( 'not_existing' );
        $this->assertQueryResult(
            array( array( 0 ) ),
            $this->handler->createSelectQuery()->select( 'COUNT( * )' )->from( 'ezuser' ),
            'Expected no existing user.'
        );
    }

    public function testUpdateUser()
    {
        $handler = $this->getUserHandler();

        $handler->createUser( $user = $this->getValidUser() );

        $user->login = 'new_login';
        $handler->updateUser( $user );

        $this->assertQueryResult(
            array( array( 42, 'kore@example.org', 'new_login', 1234567890, 'md5' ) ),
            $this->handler->createSelectQuery()->select( '*' )->from( 'ezuser' ),
            'Expected user data to be updated.'
        );
    }

    public function testSilentlyUpdateNotExistingUser()
    {
        $handler = $this->getUserHandler();
        $handler->updateUser( $this->getValidUser() );
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
}
