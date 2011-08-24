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
        $this->assertEquals( 4, $obj->policies[3]->id );
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
        $id = $obj->id;

        //assignRole( $groupId, $roleId, array $limitation = null )
        $handler->assignRole( 42, $id );// 42: Anonymous Users
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
        $id = $obj->id;
        $handler->assignRole( 1, $id );
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
        $id = $obj->id;
        $handler->assignRole( 999, $id );
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
