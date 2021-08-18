<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\Handler as SPIUserHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler as SPILocationHandler;

/**
 * Test case for Persistence\Cache\UserHandler.
 */
class UserHandlerTest extends AbstractInMemoryCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'userHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPIUserHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        $user = new User(['id' => 14, 'login' => 'otto', 'email' => 'otto@ez.no']);
        $policy = new Policy(['id' => 13, 'roleId' => 9]);
        $userToken = new User\UserTokenUpdateStruct(['userId' => 14, 'hashKey' => '4irj8t43r']);
        $escapedLogin = str_replace('@', '_A', $user->login);
        $escapedEmail = str_replace('@', '_A', $user->email);

        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, array? $key
        return [
            [
                'create',
                [$user],
                [
                    ['content', [14], false],
                    ['user', [14], true],
                    ['user_with_by_login_suffix', [$escapedLogin], true],
                    ['user_with_by_email_suffix', [$escapedEmail], true],
                ],
                ['c-14'],
                [
                    'ez-u-14',
                    'ez-u-' . $escapedLogin. '-bl',
                    'ez-u-' . $escapedEmail . '-be',
                ]
            ],
            [
                'update',
                [$user],
                [
                    ['content', [14], false],
                    ['user', [14], false],
                    ['user_with_by_email_suffix', [$escapedEmail], true],
                ],
                ['c-14', 'u-14'],
                [
                    'ez-u-' . $escapedEmail . '-be',
                ]
            ],
            [
                'updateUserToken',
                [$userToken],
                [
                    ['user_with_account_key_suffix', [14], false],
                    ['user_with_by_account_key_suffix', ['4irj8t43r'], true],
                ],
                ['u-14-ak'],
                ['ez-u-4irj8t43r-bak']
            ],
            ['expireUserToken', ['4irj8t43r'], [['user_with_by_account_key_suffix', ['4irj8t43r'], true]], null, ['ez-u-4irj8t43r-bak']],
            [
                'delete',
                [14],
                [
                    ['content', [14], false],
                    ['user', [14], false],
                ],
                ['c-14', 'u-14']
            ],
            ['createRole', [new RoleCreateStruct()]],
            ['createRoleDraft', [new RoleCreateStruct()]],
            ['loadRole', [9, 1]],
            ['loadRoleByIdentifier', ['member', 1]],
            ['loadRoleDraftByRoleId', [9]],
            ['loadRoles', []],
            ['updateRole', [new RoleUpdateStruct(['id' => 9])], [['role', [9], false]], ['r-9']],
            [
                'deleteRole',
                [9],
                [
                    ['role', [9], false],
                    ['role_assignment_role_list', [9], false],
                ],
                ['r-9', 'rarl-9']
            ],
            ['deleteRole', [9, 1]],
            ['addPolicyByRoleDraft', [9, $policy]],
            ['addPolicy', [9, $policy], [['role', [9], false]], ['r-9']],
            [
                'updatePolicy',
                [$policy],
                [
                    ['policy', [13], false],
                    ['role', [9], false],
                ],
                ['p-13', 'r-9']
            ],
            [
                'deletePolicy',
                [13, 9],
                [
                    ['policy', [13], false],
                    ['role', [9], false],
                ],
                ['p-13', 'r-9']
            ],
            ['loadPoliciesByUserId', [14]],
            [
                'unassignRole',
                [14, 9],
                [
                    ['role_assignment_group_list', [14], false],
                    ['role_assignment_role_list', [9], false],
                ],
                ['ragl-14', 'rarl-9']
            ],
            ['removeRoleAssignment', [11], [['role_assignment', [11], false]], ['ra-11']],
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
            ['load', [14], 'ez-u-14', $user],
            ['loadByLogin', ['admin'], 'ez-u-admin-bl', $user],
            ['loadByEmail', ['nospam@ez.no'], 'ez-u-nospam_Aez.no-be', [$user]],
            ['loadUserByToken', ['hash'], 'ez-u-hash-bak', $user],
            ['loadRole', [9], 'ez-r-9', $role],
            ['loadRoleByIdentifier', ['member'], 'ez-r-member-bi', $role],
            ['loadRoleAssignment', [11], 'ez-ra-11', $roleAssignment],
            ['loadRoleAssignmentsByRoleId', [9], 'ez-ra-9-bro', [$roleAssignment]],
            ['loadRoleAssignmentsByGroupId', [14], 'ez-ra-14-bg', [$roleAssignment], false, $calls],
            ['loadRoleAssignmentsByGroupId', [14, true], 'ez-ra-14-bgi', [$roleAssignment], false, $calls],
        ];
    }

    public function testPublishRoleDraftFromExistingRole()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $innerHandlerMock = $this->createMock(SPIUserHandler::class);
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
            ->with(['r-' . $originalRoleId]);
        $this->cacheMock
            ->expects($this->never())
            ->method('deleteItem');
        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->publishRoleDraft($roleDraftId);
    }

    public function testPublishNewRoleDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $innerHandlerMock = $this->createMock(SPIUserHandler::class);
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

    public function testAssignRole()
    {
        $innerUserHandlerMock = $this->createMock(SPIUserHandler::class);
        $innerLocationHandlerMock = $this->createMock(SPILocationHandler::class);

        $contentId = 14;
        $roleId = 9;

        $this->loggerMock->expects($this->once())->method('logCall');

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('userHandler')
            ->willReturn($innerUserHandlerMock);

        $innerUserHandlerMock
            ->expects($this->once())
            ->method('assignRole')
            ->with($contentId, $roleId)
            ->will($this->returnValue(null));

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('locationHandler')
            ->willReturn($innerLocationHandlerMock);

        $innerLocationHandlerMock
            ->expects($this->once())
            ->method('loadLocationsByContent')
            ->with($contentId)
            ->willReturn([new Location(['id' => '43'])]);

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['ragl-14', 'rarl-9', 'lp-43']);

        $this->cacheMock
            ->expects($this->never())
            ->method('deleteItem');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->assignRole($contentId, $roleId);
    }
}
