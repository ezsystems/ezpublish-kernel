<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\User,
    ezp\Persistence\User\Role,
    ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Policy,
    ezp\Persistence\User\Handler as UserHandlerInterface,
    ezp\Base\Exception\NotFound;

/**
 * Test case for SectionHandler using in memory storage.
 *
 */
class UserHandlerTest extends HandlerTest
{
    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::load
     */
    public function testLoad()
    {
        $obj = $this->persistenceHandler->userHandler()->load( 10 );
        $this->assertInstanceOf( 'ezp\\Persistence\\User', $obj );
        $this->assertEquals( 10, $obj->id );
        $this->assertEquals( 'nospam@ez.no', $obj->email );
        $this->assertEquals( 'anonymous', $obj->login );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadUnExistingUserId()
    {
        $this->persistenceHandler->userHandler()->load( 22 );
    }

    /**
     * Test loadByLogin function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadByLogin
     */
    public function testLoadByLogin()
    {
        $users = $this->persistenceHandler->userHandler()->loadByLogin( 'anonymous' );
        $this->assertEquals( 1, count( $users ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User', $users[0] );
        $this->assertEquals( 10, $users[0]->id );
        $this->assertEquals( 'nospam@ez.no', $users[0]->email );
        $this->assertEquals( 'anonymous', $users[0]->login );

        $users = $this->persistenceHandler->userHandler()->loadByLogin( 'anonymous', true );
        $this->assertEquals( 1, count( $users ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User', $users[0] );
        $this->assertEquals( 10, $users[0]->id );
        $this->assertEquals( 'nospam@ez.no', $users[0]->email );
        $this->assertEquals( 'anonymous', $users[0]->login );
    }

    /**
     * Test loadByLogin function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadByLogin
     */
    public function testLoadByLoginWithEmail()
    {
        $users = $this->persistenceHandler->userHandler()->loadByLogin( 'nospam@ez.no' );
        $this->assertEquals( 0, count( $users ) );

        $users = $this->persistenceHandler->userHandler()->loadByLogin( 'nospam@ez.no', true );
        $this->assertEquals( 1, count( $users ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User', $users[0] );
        $this->assertEquals( 10, $users[0]->id );
        $this->assertEquals( 'nospam@ez.no', $users[0]->email );
        $this->assertEquals( 'anonymous', $users[0]->login );
    }

    /**
     * Test loadByLogin function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadByLogin
     */
    public function testLoadByLoginUnExistingUser()
    {
        $users = $this->persistenceHandler->userHandler()->loadByLogin( 'kamelåså' );
        $this->assertEquals( array(), $users );
        $users = $this->persistenceHandler->userHandler()->loadByLogin( 'kamelåså@ez.no', true );
        $this->assertEquals( array(), $users );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::create
     */
    public function testCreate()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = new User();
        $obj->id = 1;
        $obj->email = 'unit@ez.no';
        $obj->hashAlgorithm = 2;
        $obj->login = 'unit';
        $obj->passwordHash = 'SomeRandomStuffShouldHaveBeenHash';
        $obj = $handler->create( $obj );
        $this->assertInstanceOf( 'ezp\\Persistence\\User', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'unit@ez.no', $obj->email );
        $this->assertEquals( 2, $obj->hashAlgorithm );
        $this->assertEquals( 'unit', $obj->login );
        $this->assertEquals( 'SomeRandomStuffShouldHaveBeenHash', $obj->passwordHash );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::create
     * @expectedException \ezp\Base\Exception\Logic
     */
    public function testCreateMissingId()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = new User();
        $obj->email = 'unit@ez.no';
        $obj->hashAlgorithm = 2;
        $obj->login = 'unit';
        $obj->passwordHash = 'SomeRandomStuffShouldHaveBeenHash';
        $handler->create( $obj );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::create
     * @expectedException \ezp\Base\Exception\Logic
     */
    public function testCreateExistingId()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = new User();
        $obj->id = 14;
        $obj->email = 'unit@ez.no';
        $obj->hashAlgorithm = 2;
        $obj->login = 'unit';
        $obj->passwordHash = 'SomeRandomStuffShouldHaveBeenHash';
        $handler->create( $obj );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::update
     */
    public function testUpdate()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->load( 10 );
        $obj->email = 'unit@ez.no';
        $handler->update( $obj );
        $obj = $handler->load( 10 );
        $this->assertInstanceOf( 'ezp\\Persistence\\User', $obj );
        $this->assertEquals( 10, $obj->id );
        $this->assertEquals( 'unit@ez.no', $obj->email );
        $this->assertEquals( 2, $obj->hashAlgorithm );
        $this->assertEquals( 'anonymous', $obj->login );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::delete
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDelete()
    {
        $handler = $this->persistenceHandler->userHandler();
        $handler->delete( 10 );
        $this->assertNull( $handler->load( 10 ) );
        $this->persistenceHandler->ContentHandler()->load( 10, 1 );//exception
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::createRole
     */
    public function testCreateRole()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 'test', $obj->name );
        $this->assertEquals( 3, count( $obj->policies ) );
        $this->assertEquals( $obj->id, $obj->policies[0]->roleId );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadRole
     */
    public function testLoadRole()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $obj = $handler->loadRole( $obj->id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 'test', $obj->name );
        $this->assertEquals( 3, count( $obj->policies ) );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadRole
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadRoleNotFound()
    {
        $handler = $this->persistenceHandler->userHandler();
        $handler->loadRole( 999 );//exception
    }

    /**
     * Test loadRolesByGroupId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadRolesByGroupId
     */
    public function testLoadRolesByGroupId()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 4, $obj->id );// 4: Users

        // add a policy and check that it is part of returned permission after re fetch
        $handler->addPolicy( $obj->id, new Policy( array( 'module' => 'Foo',
                                                     'function' => 'Bar',
                                                     'limitations' => array( 'Limit' => array( 'Test' ) ) ) ) );
        $list = $handler->loadRolesByGroupId( 4 );
        $this->assertEquals( 1, count( $list ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $list[0] );
        $role = $list[0];
        $this->assertEquals( 'Foo', $role->policies[3]->module );
        $this->assertEquals( 'Bar', $role->policies[3]->function );
        $this->assertEquals( array( 'Test' ), $role->policies[3]->limitations['Limit'] );
    }

    /**
     * Test loadRolesByGroupId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadRolesByGroupId
     */
    public function testLoadRolesByGroupIdEmpty()
    {
        $this->clearRolesByGroupId( 42 );

        $handler = $this->persistenceHandler->userHandler();
        $list = $handler->loadRolesByGroupId( 42 );
        $this->assertEquals( 0, count( $list ) );
        $this->assertEquals( array(), $list  );
    }

    /**
     * Test loadRolesByGroupId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadRolesByGroupId
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadRolesByGroupIdNotFound()
    {
        $handler = $this->persistenceHandler->userHandler();
        $handler->loadRolesByGroupId( 999 );
    }

    /**
     * Test loadRolesByGroupId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadRolesByGroupId
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     */
    public function testLoadRolesByGroupIdNotFoundWithCorrectType()
    {
        $handler = $this->persistenceHandler->userHandler();
        $handler->loadRolesByGroupId( 1 );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::updateRole
     */
    public function testUpdateRole()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $id = $obj->id;

        $struct = new RoleUpdateStruct();
        $struct->id = $id;
        $struct->name = 'newName';
        $handler->updateRole( $struct );
        $obj = $handler->loadRole( $id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( $id, $obj->id );
        $this->assertEquals( 'newName', $obj->name );
        $this->assertEquals( 3, count( $obj->policies ) );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::deleteRole
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDeleteRole()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );

        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $handler->loadRole( 1 ) );

        $handler->deleteRole( 1 );
        $handler->loadRole( 1 );//exception
    }

    /**
     * Test addPolicy function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::addPolicy
     */
    public function testAddPolicy()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 3, count( $obj->policies ) );
        $id = $obj->id;

        $handler->addPolicy( $id, new Policy( array( 'module' => 'Foo',
                                                     'function' => 'Bar',
                                                     'limitations' => array( 'Limit' => array( 'Test' ) ) ) ) );
        $obj = $handler->loadRole( $id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 4, count( $obj->policies ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Policy', $obj->policies[3] );
        $this->assertEquals( 'Foo', $obj->policies[3]->module );
        $this->assertEquals( $id, $obj->policies[3]->roleId );
        $this->assertEquals( 'Bar', $obj->policies[3]->function );
        $this->assertEquals( 1, count( $obj->policies[3]->limitations ) );
        $this->assertEquals( array( 'Test' ), $obj->policies[3]->limitations['Limit'] );
    }

    /**
     * Test updatePolicy function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::updatePolicy
     */
    public function testUpdatePolicy()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 3, count( $obj->policies ) );
        $this->assertEquals( 'content', $obj->policies[0]->module );
        $this->assertEquals( 'write', $obj->policies[0]->function );
        $this->assertEquals( array( 'SubTree' => array( '/1/2/' ) ), $obj->policies[0]->limitations );

        $id = $obj->id;
        $policy = $obj->policies[0];
        $policy->limitations = array( 'Node' => array( 2, 45 ) );
        $handler->updatePolicy( $policy );

        $obj = $handler->loadRole( $id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 3, count( $obj->policies ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Policy', $obj->policies[0] );
        $this->assertEquals( 'content', $obj->policies[0]->module );
        $this->assertEquals( 'write', $obj->policies[0]->function );
        $this->assertEquals( array( 'Node' => array( 2, 45 ) ), $obj->policies[0]->limitations );
    }

    /**
     * Test removePolicy function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::removePolicy
     */
    public function testRemovePolicy()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 3, count( $obj->policies ) );
        $id = $obj->id;

        $handler->removePolicy( $id, $obj->policies[2]->id );
        $obj = $handler->loadRole( $id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 2, count( $obj->policies ) );
    }

    /**
     * Test assignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::assignRole
     */
    public function testAssignRole()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users
        $obj = $handler->loadRole( $obj->id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertTrue( in_array( 42, $obj->groupIds ), 'Role was not properly assigned to User Group with id: 42' );
    }

    /**
     * Test assignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::assignRole
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     */
    public function testAssignRoleWrongGroupType()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 1, $obj->id );
    }

    /**
     * Test assignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::assignRole
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testAssignRoleGroupNotFound()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 999, $obj->id );
    }

    /**
     * Test assignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::assignRole
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testAssignRoleRoleNotFound()
    {
        $handler = $this->persistenceHandler->userHandler();
        $handler->assignRole( 42, 999 );
    }

    /**
     * Test assignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::assignRole
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     */
    public function testAssignRoleAlreadyAssigned()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users
        $handler->assignRole( 42, $obj->id );
    }

    /**
     * Test unAssignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::unAssignRole
     */
    public function testUnAssignRole()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $obj = $handler->loadRole( $obj->id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertTrue( in_array( 42, $obj->groupIds ), 'Role was not properly assigned to User Group with id: 42' );

        $handler->unAssignRole( 42, $obj->id );// 42: Anonymous Users
        $obj = $handler->loadRole( $obj->id );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertFalse( in_array( 42, $obj->groupIds ), 'Role was not properly assigned to User Group with id: 42' );
    }

    /**
     * Test unAssignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::unAssignRole
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     */
    public function testUnAssignRoleWrongGroupType()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->unAssignRole( 1, $obj->id );
    }

    /**
     * Test unAssignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::unAssignRole
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUnAssignRoleGroupNotFound()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->unAssignRole( 999, $obj->id );
    }

    /**
     * Test unAssignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::unAssignRole
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUnAssignRoleRoleNotFound()
    {
        $handler = $this->persistenceHandler->userHandler();
        $handler->unAssignRole( 42, 999 );
    }

    /**
     * Test unAssignRole function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::unAssignRole
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     */
    public function testUnAssignRoleNotAssigned()
    {
        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->unAssignRole( 42, $obj->id );// 42: Anonymous Users
    }

    /**
     * Test loadPoliciesByUserId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadPoliciesByUserId
     */
    public function testLoadPoliciesByUserId()
    {
        $this->clearRolesByGroupId( 42 );

        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $list = $handler->loadPoliciesByUserId( 10 );// 10: Anonymous User
        $this->assertEquals( 3, count( $list ) );

        // add a policy and check that it is part of returned permission after re fetch
        $handler->addPolicy( $obj->id, new Policy( array( 'module' => 'Foo',
                                                     'function' => 'Bar',
                                                     'limitations' => array( 'Limit' => array( 'Test' ) ) ) ) );
        $list = $handler->loadPoliciesByUserId( 10 );
        $this->assertEquals( 4, count( $list ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Policy', $list[3] );
        $this->assertEquals( 'Foo', $list[3]->module );
        $this->assertEquals( 'Bar', $list[3]->function );
        $this->assertEquals( array( 'Test' ), $list[3]->limitations['Limit'] );
    }

    /**
     * Test loadPoliciesByUserId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadPoliciesByUserId
     */
    public function testLoadPoliciesByUserIdDeep()
    {
        $this->clearRolesByGroupId( 42 );

        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $role = new Role();
        $role->name = 'test2';
        $role->policies = array(
            new Policy( array( 'module' => 'tag', 'function' => '*', 'limitations' => '*' ) ),
        );
        $obj = $handler->createRole( $role );
        $handler->assignRole( 4, $obj->id );// 4: Users

        $list = $handler->loadPoliciesByUserId( 10 );// 10: Anonymous User
        $this->assertEquals( 4, count( $list ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Policy', $list[3] );
        $this->assertEquals( 'tag', $list[3]->module );
        $this->assertEquals( '*', $list[3]->function );
        $this->assertEquals( '*', $list[3]->limitations );
    }

    /**
     * Test loadPoliciesByUserId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadPoliciesByUserId
     */
    public function testLoadPoliciesByUserIdDuplicates()
    {
        $this->clearRolesByGroupId( 42 );

        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $handler->assignRole( 4, $obj->id );// 4: Users

        $list = $handler->loadPoliciesByUserId( 10 );// 10: Anonymous User
        $this->assertEquals( 3, count( $list ) );
    }

    /**
     * Test loadPoliciesByUserId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadPoliciesByUserId
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadPoliciesByUserIdNotFound()
    {
        $this->persistenceHandler->userHandler()->loadPoliciesByUserId( 999 );
    }

    /**
     * Test loadPoliciesByUserId function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadPoliciesByUserId
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     */
    public function testLoadPoliciesByUserIdNotFoundWithType()
    {
        $this->persistenceHandler->userHandler()->loadPoliciesByUserId( 42 );// 42: Anonymous Users (user group)
    }

    /**
     * Test loadPoliciesByUserId function
     *
     * Make sure several policies that have same values are not merged (when not same entity)
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::loadPoliciesByUserId
     */
    public function testLoadPoliciesByUserIdWithSameValuePolicies()
    {
        $this->clearRolesByGroupId( 42 );

        $handler = $this->persistenceHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $role = new Role();
        $role->name = 'test2';
        $role->policies = array(
            new Policy( array( 'module' => $obj->policies[2]->module,
                               'function' => $obj->policies[2]->function,
                               'limitations' => $obj->policies[2]->limitations, ) ),
        );
        $obj = $handler->createRole( $role );
        $handler->assignRole( 4, $obj->id );// 4: Users

        $list = $handler->loadPoliciesByUserId( 10 );// 10: Anonymous User
        $this->assertEquals( 4, count( $list ) );
    }

    /**
     *  Create Role with content/write/SubTree:/1/2/, content/read/* and user/*\/* policy
     *
     * @return \ezp\Persistence\User\Role
     */
    protected static function getRole()
    {
        $role = new Role();
        $role->name = 'test';
        $role->policies = array(
            new Policy( array( 'module' => 'content', 'function' => 'write', 'limitations' => array( 'SubTree' => array( '/1/2/' ) ) ) ),
            new Policy( array( 'module' => 'content', 'function' => 'read', 'limitations' => '*' ) ),
            new Policy( array( 'module' => 'user', 'function' => '*', 'limitations' => '*' ) ),
        );
        return $role;
    }

    /**
     * Clear all roles (and policies) assignments on a user group
     *
     * @param mixed $groupId
     */
    protected function clearRolesByGroupId( $groupId )
    {
        $handler = $this->persistenceHandler->userHandler();
        $roles = $handler->loadRolesByGroupId( $groupId );
        foreach ( $roles as $role )
        {
            $handler->unAssignRole( $groupId, $role->id );
        }
    }
}
