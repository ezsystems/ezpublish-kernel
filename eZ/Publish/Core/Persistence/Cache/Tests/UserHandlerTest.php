<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Test case for Persistence\Cache\UserHandler.
 */
class UserHandlerTest extends HandlerTest
{
    /**
     * Commented lines represent cached functions covered by specific unit tests further down.
     *
     * @return array
     */
    public function providerForUnCachedMethods()
    {
        return array(
            //array( 'create', array( new User ) ),
            array('load', array(14)),
            array('loadByLogin', array('admin')),
            array('loadByEmail', array('admin@ez.no')),
            //array( 'update', array( new User ) ),
            //array( 'delete', array( 14 ) ),
            //array( 'createRole', array( new Role ) ),
            //array( 'loadRole', array( 22 ) ),
            array('loadRoleByIdentifier', array('users')),
            array('loadRoles', array()),
            array('loadRoleAssignmentsByRoleId', array(22)),
            //array( 'loadRoleAssignmentsByGroupId', array( 44, true ) ),
            //array( 'updateRole', array( new RoleUpdateStruct ) ),
            //array( 'deleteRole', array( 22 ) ),
            //array( 'addPolicy', array( 22, new Policy ) ),
            //array( 'updatePolicy', array( new Policy ) ),
            //array( 'deletePolicy', array( 22, 66 ) ),
            array('loadPoliciesByUserId', array(14)),
            //array( 'assignRole', array( 44, 22, array( 42 ) ) ),
            //array( 'unassignRole', array( 44, 22 ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler
     */
    public function testUnCachedMethods($method, array $arguments)
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandler));

        $expects = $innerHandler
            ->expects($this->once())
            ->method($method);

        if (isset($arguments[2])) {
            $expects->with($arguments[0], $arguments[1], $arguments[2]);
        } elseif (isset($arguments[1])) {
            $expects->with($arguments[0], $arguments[1]);
        } elseif (isset($arguments[0])) {
            $expects->with($arguments[0]);
        }

        $expects->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->userHandler();
        call_user_func_array(array($handler, $method), $arguments);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::create
     */
    public function testCreate()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User'))
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('content', 42)
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->create(new User(array('id' => 42)));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::update
     */
    public function testUpdate()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('update')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User'))
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('content', 42)
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->update(new User(array('id' => 42)));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::delete
     */
    public function testDelete()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('delete')
            ->with(14)
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('content', 14)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup', 14)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup', 'inherited', 14)
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->delete(14);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::loadRole
     */
    public function testLoadRoleIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', 33)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadRole')
            ->with(33)
            ->will(
                $this->returnValue(new Role())
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\Role'))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->loadRole(33);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::loadRole
     */
    public function testLoadRoleHasCache()
    {
        $this->loggerMock->expects($this->never())->method('logCall');

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', 33)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(new Role()));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->loadRole(33);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', 'assignments', 'byGroup', 42)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadRoleAssignmentsByGroupId')
            ->with(42, false)
            ->will(
                $this->returnValue(
                    array(
                        new RoleAssignment(array('roleId' => 33)),
                    )
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isType('array'))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId(42);

        $this->assertEquals(1, count($roleAssignments));
        $this->assertInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0]);
        $this->assertEquals(33, $roleAssignments[0]->roleId);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdHasCache()
    {
        $this->loggerMock->expects($this->never())->method('logCall');

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', 'assignments', 'byGroup', 42)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(array(new RoleAssignment(array('roleId' => 33))))
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId(42);

        $this->assertEquals(1, count($roleAssignments));
        $this->assertInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0]);
        $this->assertEquals(33, $roleAssignments[0]->roleId);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdInheritedIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', 'assignments', 'byGroup', 'inherited', '42')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadRoleAssignmentsByGroupId')
            ->with(42, true)
            ->will(
                $this->returnValue(array(new RoleAssignment(array('roleId' => 33))))
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isType('array'))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId(42, true);

        $this->assertEquals(1, count($roleAssignments));
        $this->assertInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0]);
        $this->assertEquals(33, $roleAssignments[0]->roleId);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::loadRoleAssignmentsByGroupId
     */
    public function testLoadRoleAssignmentsByGroupIdInheritedHasCache()
    {
        $this->loggerMock->expects($this->never())->method('logCall');

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', 'assignments', 'byGroup', 'inherited', '42')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(array(new RoleAssignment(array('roleId' => 33))))
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->userHandler();
        $roleAssignments = $handler->loadRoleAssignmentsByGroupId(42, true);

        $this->assertEquals(1, count($roleAssignments));
        $this->assertInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\RoleAssignment', $roleAssignments[0]);
        $this->assertEquals(33, $roleAssignments[0]->roleId);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::createRole
     */
    public function testCreateRole()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('createRole')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\RoleCreateStruct'))
            ->will(
                $this->returnValue(
                    new Role(
                        ['id' => 33, 'name' => 'Editors', 'identifier' => 'intranet', 'status' => Role::STATUS_DEFINED]
                    )
                )
            );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->never())
            ->method('getItem');

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->createRole(new User\RoleCreateStruct());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::createRoleDraft
     */
    public function testCreateRoleDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('createRoleDraft')
            ->with(33)
            ->will(
                $this->returnValue(
                    new Role(
                        ['id' => 33, 'name' => 'Editors', 'identifier' => 'intranet', 'status' => Role::STATUS_DRAFT]
                    )
                )
            );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->never())
            ->method('getItem');

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->userHandler();
        $role = new Role(['id' => 33]);
        $handler->createRoleDraft($role->id);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::updateRole
     */
    public function testUpdateRole()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('updateRole')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\RoleUpdateStruct'));

        $roleUpdateStruct = new RoleUpdateStruct();
        $roleUpdateStruct->id = 42;

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('user', 'role', $roleUpdateStruct->id)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->never())
            ->method('getItem');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->updateRole($roleUpdateStruct);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::updateRole
     */
    public function testUpdateRoleDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('updateRole')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\RoleUpdateStruct'));

        $roleUpdateStruct = new RoleUpdateStruct();
        $roleUpdateStruct->id = 42;

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('user', 'role', $roleUpdateStruct->id)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->never())
            ->method('getItem');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->updateRole($roleUpdateStruct);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::deleteRole
     */
    public function testDeleteRole()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteRole')
            ->with(33)
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('user', 'role', 33)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('user', 'role', 'assignments')
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->deleteRole(33);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::deleteRole
     */
    public function testDeleteRoleDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteRole')
            ->with(33, Role::STATUS_DRAFT)
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->never())
            ->method('clear');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->deleteRole(33, Role::STATUS_DRAFT);
    }

    public function testPublishRoleDraftFromExistingRole()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->willReturn($innerHandlerMock);

        $roleDraftId = 33;
        $originalRoleId = 30;
        $innerHandlerMock
            ->expects($this->exactly(2))
            ->method('loadRole')
            ->willReturnMap([
                [$roleDraftId, Role::STATUS_DRAFT, new Role(['originalId' => $originalRoleId])],
                [$originalRoleId, Role::STATUS_DEFINED, new Role(['id' => $originalRoleId])],
            ]);
        $innerHandlerMock
            ->expects($this->once())
            ->method('publishRoleDraft')
            ->with($roleDraftId);

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('user', 'role', 'assignments')
            ->will($this->returnValue(true));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', $originalRoleId)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\Role'))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->publishRoleDraft($roleDraftId);
    }

    public function testPublishNewRoleDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->willReturn($innerHandlerMock);

        $roleDraftId = 33;
        $originalRoleId = -1;
        $innerHandlerMock
            ->expects($this->at(0))
            ->method('loadRole')
            ->with($roleDraftId, Role::STATUS_DRAFT)
            ->willReturn(new Role(['originalId' => $originalRoleId]));
        $innerHandlerMock
            ->expects($this->at(1))
            ->method('publishRoleDraft')
            ->with($roleDraftId);
        $innerHandlerMock
            ->expects($this->at(2))
            ->method('loadRole')
            ->with($originalRoleId, Role::STATUS_DEFINED)
            ->willThrowException(new NotFoundException('foo', 'bar'));
        $innerHandlerMock
            ->expects($this->at(3))
            ->method('loadRole')
            ->with($roleDraftId, Role::STATUS_DEFINED)
            ->willReturn(new Role(['id' => $roleDraftId]));

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('user', 'role', 'assignments')
            ->will($this->returnValue(true));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('user', 'role', $roleDraftId)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\Role'))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->publishRoleDraft($roleDraftId);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::addPolicy
     */
    public function testAddPolicy()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('addPolicy')
            ->with(33, $this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\Policy'))
            ->will(
                $this->returnValue(new Policy())
            );

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('user', 'role', 33)
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->addPolicy(33, new Policy());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::addPolicyByRoleDraft
     */
    public function testAddPolicyByRoleDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('addPolicyByRoleDraft')
            ->with(33, $this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\Policy'))
            ->will(
                $this->returnValue(new Policy())
            );

        $this->cacheMock
            ->expects($this->never())
            ->method('clear');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->addPolicyByRoleDraft(33, new Policy());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::updatePolicy
     */
    public function testUpdatePolicy()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('updatePolicy')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\Policy'))
            ->will(
                $this->returnValue(new Policy(array('roleId' => 33)))
            );

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('user', 'role', 33)
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->updatePolicy(new Policy(array('roleId' => 33)));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::deletePolicy
     */
    public function testDeletePolicy()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('deletePolicy')
            ->with(55, 33)
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('user', 'role')
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->deletePolicy(55, 33);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::assignRole
     */
    public function testAssignRole()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('assignRole')
            ->with(33, 22)
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('user', 'role', 22)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup', 33)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup', 'inherited')
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->assignRole(33, 22);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UserHandler::unassignRole
     */
    public function testUnassignRole()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\User\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('unassignRole')
            ->with(33, 22)
            ->will(
                $this->returnValue(true)
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('user', 'role', 22)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup', 33)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('user', 'role', 'assignments', 'byGroup', 'inherited')
            ->will($this->returnValue(true));

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->unassignRole(33, 22);
    }
}
