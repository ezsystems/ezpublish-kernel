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

        // string $method, array $arguments, array? $tagGeneratingArguments, array? $keyGeneratingArguments, array? $tags, array? $key, ?mixed $returnValue
        return [
            [
                'create',
                [$user],
                [
                    ['content', [14], false],
                ],
                [
                    ['user', [14], true],
                    ['user_with_by_login_suffix', [$escapedLogin], true],
                    ['user_with_by_email_suffix', [$escapedEmail], true],
                ],
                ['c-14'],
                [
                    'ibx-u-14',
                    'ibx-u-' . $escapedLogin . '-bl',
                    'ibx-u-' . $escapedEmail . '-be',
                ],
            ],
            [
                'update',
                [$user],
                [
                    ['content', [14], false],
                    ['user', [14], false],
                ],
                [
                    ['user_with_by_email_suffix', [$escapedEmail], true],
                ],
                ['c-14', 'u-14'],
                [
                    'ibx-u-' . $escapedEmail . '-be',
                ],
            ],
            [
                'updateUserToken',
                [$userToken],
                [
                    ['user_with_account_key_suffix', [14], false],
                ],
                [
                    ['user_with_by_account_key_suffix', ['4irj8t43r'], true],
                ],
                ['u-14-ak'],
                ['ibx-u-4irj8t43r-bak'],
            ],
            ['expireUserToken', ['4irj8t43r'], null, [['user_with_by_account_key_suffix', ['4irj8t43r'], true]], null, ['ibx-u-4irj8t43r-bak']],
            [
                'delete',
                [14],
                [
                    ['content', [14], false],
                    ['user', [14], false],
                ],
                null,
                ['c-14', 'u-14'],
            ],
            ['createRole', [new RoleCreateStruct()]],
            ['createRoleDraft', [new RoleCreateStruct()]],
            ['loadRole', [9, 1]],
            ['loadRoleByIdentifier', ['member', 1]],
            ['loadRoleDraftByRoleId', [9]],
            ['loadRoles', []],
            ['updateRole', [new RoleUpdateStruct(['id' => 9])], [['role', [9], false]], null, ['r-9']],
            [
                'deleteRole',
                [9],
                [
                    ['role', [9], false],
                    ['role_assignment_role_list', [9], false],
                ],
                null,
                ['r-9', 'rarl-9'],
            ],
            ['deleteRole', [9, 1]],
            ['addPolicyByRoleDraft', [9, $policy]],
            ['addPolicy', [9, $policy], [['role', [9], false]], null, ['r-9']],
            [
                'updatePolicy',
                [$policy],
                [
                    ['policy', [13], false],
                    ['role', [9], false],
                ],
                null,
                ['p-13', 'r-9'],
            ],
            [
                'deletePolicy',
                [13, 9],
                [
                    ['policy', [13], false],
                    ['role', [9], false],
                ],
                null,
                ['p-13', 'r-9'],
            ],
            ['loadPoliciesByUserId', [14]],
            [
                'unassignRole',
                [14, 9],
                [
                    ['role_assignment_group_list', [14], false],
                    ['role_assignment_role_list', [9], false],
                ],
                null,
                ['ragl-14', 'rarl-9'],
            ],
            ['removeRoleAssignment', [11], [['role_assignment', [11], false]], null, ['ra-11']],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $user = new User(['id' => 14]);
        $role = new Role(['id' => 9]);
        $roleAssignment = new RoleAssignment(['id' => 11, 'roleId' => 9, 'contentId' => 14]);
        $calls = [['locationHandler', Location\Handler::class, 'loadLocationsByContent', [new Location(['pathString' => '/1/2/43/'])]]];

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingArguments, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            ['load', [14], 'ibx-u-14', null, null, [['user', [], true]], ['ibx-u'], $user],
            [
                'loadByLogin',
                ['admin'],
                'ibx-u-admin-bl',
                null,
                null,
                [
                    ['user', [], true],
                    ['by_login_suffix', [], false],
                ],
                ['ibx-u', 'bl'],
                $user,
            ],
            ['loadByEmail', ['nospam@ez.no'], 'ibx-u-nospam_Aez.no-be', null, null, [['user_with_by_email_suffix', ['nospam_Aez.no'], true]], ['ibx-u-nospam_Aez.no-be'], [$user]],
            [
                'loadUserByToken',
                ['hash'],
                'ibx-u-hash-bak',
                null,
                null,
                [
                    ['user', [], true],
                    ['by_account_key_suffix', [], false],
                ],
                ['ibx-u', '-bak'],
                $user,
            ],
            ['loadRole', [9], 'ibx-r-9', null, null, [['role', [], true]], ['ibx-r'], $role],
            [
                'loadRoleByIdentifier',
                ['member'],
                'ibx-r-member-bi',
                null,
                null,
                [
                    ['role', [], true],
                    ['by_identifier_suffix', [], false],
                ],
                ['ibx-r', '-bi'],
                $role,
            ],
            ['loadRoleAssignment', [11], 'ibx-ra-11', null, null, [['role_assignment', [], true]], ['ibx-ra'], $roleAssignment],
            ['loadRoleAssignmentsByRoleId', [9], 'ibx-ra-9-bro', null, null, [['role_assignment_with_by_role_suffix', [9], true]], ['ibx-ra-9-bro'], [$roleAssignment]],
            [
                'loadRoleAssignmentsByGroupId',
                [14],
                'ibx-ra-14-bg',
                null,
                null,
                [
                    ['role_assignment_with_by_group_suffix', [14], true],
                ],
                ['ibx-ra-14-bg'],
                [$roleAssignment],
                false,
                $calls,
            ],
            [
                'loadRoleAssignmentsByGroupId',
                [14, true],
                'ibx-ra-14-bgi',
                null,
                null,
                [['role_assignment_with_by_group_inherited_suffix', [14], true]],
                ['ibx-ra-14-bgi'],
                [$roleAssignment],
                false,
                $calls,
            ],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $user = new User(['id' => 14]);
        $role = new Role(['id' => 9]);
        $roleAssignment = new RoleAssignment(['id' => 11, 'roleId' => 9, 'contentId' => 14]);
        $calls = [['locationHandler', Location\Handler::class, 'loadLocationsByContent', [new Location(['pathString' => '/1/2/43/'])]]];

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingArguments, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            [
                'load',
                [14],
                'ibx-u-14',
                [
                    ['content', [14], false],
                    ['user', [14], false],
                ],
                ['c-14', 'u-14'],
                [
                    ['user', [], true],
                ],
                ['ibx-u'],
                $user,
            ],
            [
                'loadByLogin',
                ['admin'],
                'ibx-u-admin-bl',
                [
                    ['content', [14], false],
                    ['user', [14], false],
                ],
                ['c-14', 'u-14'],
                [
                    ['user', [], true],
                    ['by_login_suffix', [], false],
                ],
                ['ibx-u', 'bl'],
                $user,
            ],
            [
                'loadByEmail',
                ['nospam@ez.no'],
                'ibx-u-nospam_Aez.no-be',
                [
                    ['content', [14], false],
                    ['user', [14], false],
                ],
                ['c-14', 'u-14'],
                [
                    ['user_with_by_email_suffix', ['nospam_Aez.no'], true],
                ],
                ['ibx-u-nospam_Aez.no-be'],
                [$user],
            ],
            [
                'loadUserByToken',
                ['hash'],
                'ibx-u-hash-bak',
                [
                    ['content', [14], false],
                    ['user', [14], false],
                    ['user_with_account_key_suffix', [14], false],
                ],
                ['c-14', 'u-14', 'u-14-bak'],
                [
                    ['user', [], true],
                    ['by_account_key_suffix', [], false],
                ],
                ['ibx-u', '-bak'],
                $user,
            ],
            [
                'loadRole',
                [9],
                'ibx-r-9',
                [
                    ['role', [9], false],
                ],
                ['r-9'],
                [
                    ['role', [], true],
                ],
                ['ibx-r'],
                $role,
            ],
            [
                'loadRoleByIdentifier',
                ['member'],
                'ibx-r-member-bi',
                [
                    ['role', [9], false],
                ],
                ['r-9'],
                [
                    ['role', [], true],
                    ['by_identifier_suffix', [], false],
                ],
                ['ibx-r', '-bi'],
                $role,
            ],
            [
                'loadRoleAssignment',
                [11],
                'ibx-ra-11',
                [
                    ['role_assignment', [11], false],
                    ['role_assignment_group_list', [14], false],
                    ['role_assignment_role_list', [9], false],
                ],
                ['ra-11', 'ragl-14', 'rarl-9'],
                [
                    ['role_assignment', [], true],
                ],
                ['ibx-ra'],
                $roleAssignment,
            ],
            [
                'loadRoleAssignmentsByRoleId',
                [9],
                'ibx-ra-9-bro',
                [
                    ['role_assignment_role_list', [9], false],
                    ['role', [9], false],
                    ['role_assignment', [11], false],
                    ['role_assignment_group_list', [14], false],
                    ['role_assignment_role_list', [9], false],
                ],
                ['rarl-9', 'r-9', 'ra-11', 'ragl-14', 'rarl-9'],
                [
                    ['role_assignment_with_by_role_suffix', [9], true],
                ],
                ['ibx-ra-9-bro'],
                [$roleAssignment],
            ],
            [
                'loadRoleAssignmentsByGroupId',
                [14],
                'ibx-ra-14-bg',
                [
                    ['role_assignment_group_list', [14], false],
                    ['location_path', ['2'], false],
                    ['location_path', ['43'], false],
                    ['role_assignment', [11], false],
                    ['role_assignment_group_list', [14], false],
                    ['role_assignment_role_list', [9], false],
                ],
                ['ragl-14', 'lp-2', 'lp-43', 'ra-11', 'ragl-14', 'rarl-9'],
                [
                    ['role_assignment_with_by_group_suffix', [14], true],
                ],
                ['ibx-ra-14-bg'],
                [$roleAssignment],
                false,
                $calls,
            ],
            [
                'loadRoleAssignmentsByGroupId',
                [14, true],
                'ibx-ra-14-bgi',
                [
                    ['role_assignment_group_list', [14], false],
                    ['location_path', ['2'], false],
                    ['location_path', ['43'], false],
                    ['role_assignment', [11], false],
                    ['role_assignment_group_list', [14], false],
                    ['role_assignment_role_list', [9], false],
                ],
                ['ragl-14', 'lp-2', 'lp-43', 'ra-11', 'ragl-14', 'rarl-9'],
                [
                    ['role_assignment_with_by_group_inherited_suffix', [14], true],
                ],
                ['ibx-ra-14-bgi'],
                [$roleAssignment],
                false,
                $calls,
            ],
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

        $roleTag = 'r-' . $originalRoleId;

        $this->cacheIdentifierGeneratorMock
            ->expects($this->once())
            ->method('generateTag')
            ->with('role', [$originalRoleId], false)
            ->willReturn($roleTag);

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with([$roleTag]);

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
            ->willReturn(null);

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('locationHandler')
            ->willReturn($innerLocationHandlerMock);

        $innerLocationHandlerMock
            ->expects($this->once())
            ->method('loadLocationsByContent')
            ->with($contentId)
            ->willReturn([new Location(['id' => '43'])]);

        $tags = ['ragl-14', 'rarl-9', 'lp-43'];

        $this->cacheIdentifierGeneratorMock
            ->expects($this->exactly(3))
            ->method('generateTag')
            ->withConsecutive(
                ['role_assignment_group_list', [14], false],
                ['role_assignment_role_list', [9], false],
                ['location_path', [43], false]
            )
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cacheMock
            ->expects($this->never())
            ->method('deleteItem');

        $handler = $this->persistenceCacheHandler->userHandler();
        $handler->assignRole($contentId, $roleId);
    }
}
