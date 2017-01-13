<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Handler;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Test case for Persistence\Cache\UserHandler.
 */
class UserHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'userHandler';
    }

    public function getHandlerClassName(): string
    {
        return Handler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        $user = new User(['id' => 14]);
        $policy = new Policy(['id' => 13, 'roleId' => 9]);

        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['create', [$user], ['content-fields-14']],
            ['update', [$user], ['content-fields-14', 'user-14']],
            ['delete', [14], ['content-fields-14', 'user-14']],
            ['createRole', [new RoleCreateStruct()]],
            ['createRoleDraft', [new RoleCreateStruct()]],
            ['loadRole', [9, 1]],
            ['loadRoleByIdentifier', ['member', 1]],
            ['loadRoleDraftByRoleId', [9]],
            ['loadRoles', []],
            ['updateRole', [new RoleUpdateStruct(['id' => 9])], ['role-9']],
            ['deleteRole', [9], ['role-9', 'role-assignment-role-list-9']],
            ['deleteRole', [9, 1]],
            ['addPolicyByRoleDraft', [9, $policy]],
            ['addPolicy', [9, $policy], ['role-9']],
            ['updatePolicy', [$policy], ['policy-13', 'role-9']],
            ['deletePolicy', [13, 9], ['policy-13', 'role-9']],
            ['loadPoliciesByUserId', [14]],
            ['assignRole', [14, 9], ['role-assignment-group-list-14', 'role-assignment-role-list-9']],
            ['unassignRole', [14, 9], ['role-assignment-group-list-14', 'role-assignment-role-list-9']],
            ['removeRoleAssignment', [11], ['role-assignment-11']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $user = new User(['id' => 14]);
        $role = new Role(['id' => 9]);
        $roleAssignment = new RoleAssignment(['id' => 11, 'roleId' => 9, 'contentId' => 14]);
        $calls = [['locationHandler', Location\Handler::class, 'loadLocationsByContent', [new Location(['pathString' => '/1/2/43/'])]]];

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['load', [14], 'ez-user-14', $user],
            ['loadByLogin', ['admin'], 'ez-user-admin-by-login', $user],
            ['loadByEmail', ['nospam@ez.no'], 'ez-user-nospam§ez.no-by-email', [$user]],
            ['loadRole', [9], 'ez-role-9', $role],
            ['loadRoleByIdentifier', ['member'], 'ez-role-member-by-identifier', $role],
            ['loadRoleAssignment', [11], 'ez-role-assignment-11', $roleAssignment],
            ['loadRoleAssignmentsByRoleId', [9], 'ez-role-assignment-9-by-role', [$roleAssignment]],
            ['loadRoleAssignmentsByGroupId', [14], 'ez-role-assignment-14-by-group', [$roleAssignment], false, $calls],
            ['loadRoleAssignmentsByGroupId', [14, true], 'ez-role-assignment-14-by-group-inherited', [$roleAssignment], false, $calls],
        ];
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
            ->expects($this->once())
            ->method('loadRole')
            ->with($roleDraftId, Role::STATUS_DRAFT)
            ->willReturn(new Role(['originalId' => $originalRoleId]));

        $innerHandlerMock
            ->expects($this->once())
            ->method('publishRoleDraft')
            ->with($roleDraftId);

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['role-' . $originalRoleId]);

        $this->cacheMock
            ->expects($this->never())
            ->method('deleteItem');

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
        $innerHandlerMock
            ->expects($this->at(0))
            ->method('loadRole')
            ->with($roleDraftId, Role::STATUS_DRAFT)
            ->willReturn(new Role(['originalId' => -1]));

        $innerHandlerMock
            ->expects($this->at(1))
            ->method('publishRoleDraft')
            ->with($roleDraftId);

        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->publishRoleDraft($roleDraftId);
    }
}
