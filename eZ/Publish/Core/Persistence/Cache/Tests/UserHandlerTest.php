<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Test case for Persistence\Cache\UserHandler
 */
class UserHandlerTest extends HandlerTest
{
    /**
     * @return array
     */
    function providerForUnCachedMethods()
    {
        return array(
            array( 'create', array( new User ) ),
            array( 'load', array( 14 ) ),
            array( 'loadByLogin', array( 'admin', true ) ),
            array( 'update', array( new User ) ),
            //array( 'delete', array( 14 ) ),
            //array( 'createRole', array( new Role ) ),
            //array( 'loadRole', array( 22 ) ),
            array( 'loadRoleByIdentifier', array( 'users' ) ),
            array( 'loadRoles', array() ),
            array( 'loadRoleAssignmentsByRoleId', array( 22 ) ),
            //array( 'loadRoleAssignmentsByGroupId', array( 44, true ) ),
            //array( 'updateRole', array( new RoleUpdateStruct ) ),
            //array( 'deleteRole', array( 22 ) ),
            //array( 'addPolicy', array( 22, new Policy ) ),
            //array( 'updatePolicy', array( new Policy ) ),
            //array( 'removePolicy', array( 22, 66 ) ),
            array( 'loadPoliciesByUserId', array( 14 ) ),
            //array( 'assignRole', array( 44, 22, array( 42 ) ) ),
            //array( 'unAssignRole', array( 44, 22 ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler
     */
    public function testUnCachedMethods( $method, array $arguments )
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $expects = $innerHandler
            ->expects( $this->once() )
            ->method( $method );

        if ( isset( $arguments[2] ) )
            $expects->with( $arguments[0], $arguments[1], $arguments[2] );
        else if ( isset( $arguments[1] ) )
            $expects->with( $arguments[0], $arguments[1] );
        else if ( isset( $arguments[0] ) )
            $expects->with( $arguments[0] );

        $expects->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->userHandler();
        call_user_func_array( array( $handler, $method ), $arguments );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::delete
     */
    public function testDelete()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'delete' )
            ->with( 14 )
            ->will(
                $this->returnValue( true )
            );

        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 14 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 'inherited', 14 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->delete( 14 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::loadRole
     */
    public function testLoadRoleIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'loadRole' )
            ->with( 33 )
            ->will(
                $this->returnValue( new Role )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Role' ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->loadRole( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::loadRole
     */
    public function testLoadRoleHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( new Role ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything()  );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->userHandler();
        $handler->loadRole( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 42 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'loadRoleAssignmentsByGroupId' )
            ->with( 42, false )
            ->will(
                $this->returnValue(
                    array(
                        new RoleAssignment( array( 'roleId' => 33 ) )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isType( 'array' ) );

        $handler = $this->persistenceHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId( 42 );

        $this->assertEquals( 1, count( $roleAssignments ) );
        $this->assertInstanceOf( '\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0] );
        $this->assertEquals( 33, $roleAssignments[0]->roleId );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 42 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will(
                $this->returnValue( array( new RoleAssignment( array( 'roleId' => 33 ) ) ) )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything()  );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId( 42 );

        $this->assertEquals( 1, count( $roleAssignments ) );
        $this->assertInstanceOf( '\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0] );
        $this->assertEquals( 33, $roleAssignments[0]->roleId );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdInheritedIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 'inherited/42' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'loadRoleAssignmentsByGroupId' )
            ->with( 42, true )
            ->will(
                $this->returnValue( array( new RoleAssignment( array( 'roleId' => 33 ) ) ) )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isType( 'array' ) );

        $handler = $this->persistenceHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId( 42, true );

        $this->assertEquals( 1, count( $roleAssignments ) );
        $this->assertInstanceOf( '\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0] );
        $this->assertEquals( 33, $roleAssignments[0]->roleId );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdInheritedHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 'inherited/42' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will(
                $this->returnValue( array( new RoleAssignment( array( 'roleId' => 33 ) ) ) )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything()  );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId( 42, true );

        $this->assertEquals( 1, count( $roleAssignments ) );
        $this->assertInstanceOf( '\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0] );
        $this->assertEquals( 33, $roleAssignments[0]->roleId );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::createRole
     */
    public function testCreateRole()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'createRole' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Role' ) )
            ->will(
                $this->returnValue(
                    new Role(
                        array( 'id' => 33, 'name' => 'Editors', 'identifier' => 'intranet'  )
                    )
                )
            );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Role' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->userHandler();
        $handler->createRole( new Role() );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::updateRole
     */
    public function testUpdateRole()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'updateRole' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\RoleUpdateStruct' ) )
            ->will(
                $this->returnValue(
                    new Role(
                        array( 'id' => 33, 'name' => 'Old Intranet', 'identifier' => 'old_intranet'  )
                    )
                )
            );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Role' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->userHandler();
        $handler->updateRole( new RoleUpdateStruct() );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::deleteRole
     */
    public function testDeleteRole()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'deleteRole' )
            ->with( 33 )
            ->will(
                $this->returnValue( true )
            );

        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 'assignments' )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->deleteRole( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::addPolicy
     */
    public function testAddPolicy()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'addPolicy' )
            ->with( 33, $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Policy' ) )
            ->will(
                $this->returnValue( new Policy() )
            );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->addPolicy( 33, new Policy() );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::updatePolicy
     */
    public function testUpdatePolicy()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'updatePolicy' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Policy' ) )
            ->will(
                $this->returnValue( new Policy( array( 'roleId' => 33 ) ) )
            );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->updatePolicy( new Policy( array( 'roleId' => 33 ) ) );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::removePolicy
     */
    public function testRemovePolicy()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'removePolicy' )
            ->with( 33, 55 )
            ->will(
                $this->returnValue( true )
            );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'user', 'role', 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->removePolicy( 33, 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::assignRole
     */
    public function testAssignRole()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'assignRole' )
            ->with( 33, 22 )
            ->will(
                $this->returnValue( true )
            );

        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 22 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 33 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 2 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 'inherited' )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->assignRole( 33, 22 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UserHandler::unAssignRole
     */
    public function testUnAssignRole()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUserHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'unAssignRole' )
            ->with( 33, 22 )
            ->will(
                $this->returnValue( true )
            );

        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 22 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 33 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 2 ) )
            ->method( 'clear' )
            ->with( 'user', 'role', 'assignments', 'byGroup', 'inherited' )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->userHandler();
        $handler->unAssignRole( 33, 22 );
    }
}
