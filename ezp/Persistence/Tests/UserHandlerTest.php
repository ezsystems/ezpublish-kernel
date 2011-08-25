<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
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
        $obj = $this->repositoryHandler->userHandler()->load( 10 );
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
        $this->repositoryHandler->userHandler()->load( 22 );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::create
     */
    public function testCreate()
    {
        $handler = $this->repositoryHandler->userHandler();
        $obj = new User();
        $obj->email = 'unit@ez.no';
        $obj->hashAlgorithm = 2;
        $obj->login = 'unit';
        $obj->password = 'SomeRandomStuffShouldHaveBeenHash';
        $obj = $handler->create( $obj );
        $this->assertInstanceOf( 'ezp\\Persistence\\User', $obj );
        $this->assertEquals( 15, $obj->id );
        $this->assertEquals( 'unit@ez.no', $obj->email );
        $this->assertEquals( 2, $obj->hashAlgorithm );
        $this->assertEquals( 'unit', $obj->login );
        $this->assertEquals( 'SomeRandomStuffShouldHaveBeenHash', $obj->password );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::update
     */
    public function testUpdate()
    {
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
        $handler->delete( 10 );
        $this->assertNull( $handler->load( 10 ) );
        $this->repositoryHandler->ContentHandler()->load( 10, 1 );//exception
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::createRole
     */
    public function testCreateRole()
    {
        $handler = $this->repositoryHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 1, $obj->id );
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
        $handler = $this->repositoryHandler->userHandler();
        $handler->loadRole( 1 );//exception
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::updateRole
     */
    public function testUpdateRole()
    {
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $this->assertEquals( 'Bar', $obj->policies[3]->function );
        $this->assertEquals( 1, count( $obj->policies[3]->limitations ) );
        $this->assertEquals( array( 'Test' ), $obj->policies[3]->limitations['Limit'] );
    }

    /**
     * Test removePolicy function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::removePolicy
     */
    public function testRemovePolicy()
    {
        $handler = $this->repositoryHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Role', $obj );
        $this->assertEquals( 3, count( $obj->policies ) );
        $id = $obj->id;

        $handler->removePolicy( $id, 3 );
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
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
        $handler = $this->repositoryHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->unAssignRole( 42, $obj->id );// 42: Anonymous Users
    }

    /**
     * Test getPermissions function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::getPermissions
     */
    public function testGetPermissions()
    {
        $handler = $this->repositoryHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $list = $handler->getPermissions( 10 );// 10: Anonymous User
        $this->assertEquals( 3, count( $list ) );

        // add a policy and check that it is part of returned permission after re fetch
        $handler->addPolicy( $obj->id, new Policy( array( 'module' => 'Foo',
                                                     'function' => 'Bar',
                                                     'limitations' => array( 'Limit' => array( 'Test' ) ) ) ) );
        $list = $handler->getPermissions( 10 );
        $this->assertEquals( 4, count( $list ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Policy', $list[3] );
        $this->assertEquals( 'Foo', $list[3]->module );
        $this->assertEquals( 'Bar', $list[3]->function );
        $this->assertEquals( array( 'Test' ), $list[3]->limitations['Limit'] );
    }

    /**
     * Test getPermissions function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::getPermissions
     */
    public function testGetPermissionsDeep()
    {
        $handler = $this->repositoryHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $role = new Role();
        $role->name = 'test2';
        $role->policies = array(
            new Policy( array( 'module' => 'tag', 'function' => '*', 'limitations' => '*' ) ),
        );
        $obj = $handler->createRole( $role );
        $handler->assignRole( 4, $obj->id );// 4: Users

        $list = $handler->getPermissions( 10 );// 10: Anonymous User
        $this->assertEquals( 4, count( $list ) );
        $this->assertInstanceOf( 'ezp\\Persistence\\User\\Policy', $list[3] );
        $this->assertEquals( 'tag', $list[3]->module );
        $this->assertEquals( '*', $list[3]->function );
        $this->assertEquals( '*', $list[3]->limitations );
    }

    /**
     * Test getPermissions function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::getPermissions
     */
    public function testGetPermissionsDuplicates()
    {
        $handler = $this->repositoryHandler->userHandler();
        $obj = $handler->createRole( self::getRole() );
        $handler->assignRole( 42, $obj->id );// 42: Anonymous Users

        $handler->assignRole( 4, $obj->id );// 4: Users

        $list = $handler->getPermissions( 10 );// 10: Anonymous User
        $this->assertEquals( 3, count( $list ) );
    }


    /**
     * Test getPermissions function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::getPermissions
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testGetPermissionsNotFound()
    {
        $this->repositoryHandler->userHandler()->getPermissions( 999 );
    }

    /**
     * Test getPermissions function
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::getPermissions
     * @expectedException \ezp\Base\Exception\NotFoundWithType
     */
    public function testGetPermissionsNotFoundWithType()
    {
        $this->repositoryHandler->userHandler()->getPermissions( 42 );// 42: Anonymous Users (user group)
    }

    /**
     * Test getPermissions function
     *
     * Make sure several policies that have same values are not merged (when not same entity)
     *
     * @covers ezp\Persistence\Storage\InMemory\UserHandler::getPermissions
     */
    public function testGetPermissionsWithSameValuePolicies()
    {
        $handler = $this->repositoryHandler->userHandler();
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

        $list = $handler->getPermissions( 10 );// 10: Anonymous User
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
}
