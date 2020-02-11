<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\SPI\Repository\Decorator\UserServiceDecorator;

class UserServiceDecoratorTest extends TestCase
{
    protected function createDecorator(MockObject $service): UserService
    {
        return new class($service) extends UserServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(UserService::class);
    }

    public function testCreateUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $serviceMock->expects($this->once())->method('createUserGroup')->with(...$parameters);

        $decoratedService->createUserGroup(...$parameters);
    }

    public function testLoadUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce176350.26344745',
            ['random_value_5ced05ce176389.48271998'],
        ];

        $serviceMock->expects($this->once())->method('loadUserGroup')->with(...$parameters);

        $decoratedService->loadUserGroup(...$parameters);
    }

    public function testLoadSubUserGroupsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(UserGroup::class),
            'random_value_5ced05ce1763e8.82084712',
            'random_value_5ced05ce1763f9.17530594',
            ['random_value_5ced05ce176401.55725588'],
        ];

        $serviceMock->expects($this->once())->method('loadSubUserGroups')->with(...$parameters);

        $decoratedService->loadSubUserGroups(...$parameters);
    }

    public function testDeleteUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(UserGroup::class)];

        $serviceMock->expects($this->once())->method('deleteUserGroup')->with(...$parameters);

        $decoratedService->deleteUserGroup(...$parameters);
    }

    public function testMoveUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroup::class),
        ];

        $serviceMock->expects($this->once())->method('moveUserGroup')->with(...$parameters);

        $decoratedService->moveUserGroup(...$parameters);
    }

    public function testUpdateUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateUserGroup')->with(...$parameters);

        $decoratedService->updateUserGroup(...$parameters);
    }

    public function testCreateUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            ['random_value_5ced05ce177102.13726421'],
        ];

        $serviceMock->expects($this->once())->method('createUser')->with(...$parameters);

        $decoratedService->createUser(...$parameters);
    }

    public function testLoadUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce177160.22585046',
            ['random_value_5ced05ce177174.42173129'],
        ];

        $serviceMock->expects($this->once())->method('loadUser')->with(...$parameters);

        $decoratedService->loadUser(...$parameters);
    }

    public function testCheckUserCredentialsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            'random_value_5ced05ce1771c7.58152750',
        ];

        $serviceMock->expects($this->once())->method('checkUserCredentials')->with(...$parameters);

        $decoratedService->checkUserCredentials(...$parameters);
    }

    public function testLoadUserByLoginDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce177219.33403208',
            ['random_value_5ced05ce177226.14195829'],
        ];

        $serviceMock->expects($this->once())->method('loadUserByLogin')->with(...$parameters);

        $decoratedService->loadUserByLogin(...$parameters);
    }

    public function testLoadUsersByEmailDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce177244.39880595',
            ['random_value_5ced05ce177252.76037474'],
        ];

        $serviceMock->expects($this->once())->method('loadUsersByEmail')->with(...$parameters);

        $decoratedService->loadUsersByEmail(...$parameters);
    }

    public function testLoadUserByTokenDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce177277.70284488',
            ['random_value_5ced05ce177287.80858763'],
        ];

        $serviceMock->expects($this->once())->method('loadUserByToken')->with(...$parameters);

        $decoratedService->loadUserByToken(...$parameters);
    }

    public function testDeleteUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(User::class)];

        $serviceMock->expects($this->once())->method('deleteUser')->with(...$parameters);

        $decoratedService->deleteUser(...$parameters);
    }

    public function testUpdateUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateUser')->with(...$parameters);

        $decoratedService->updateUser(...$parameters);
    }

    public function testUpdateUserTokenDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateUserToken')->with(...$parameters);

        $decoratedService->updateUserToken(...$parameters);
    }

    public function testExpireUserTokenDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce177e70.34830190'];

        $serviceMock->expects($this->once())->method('expireUserToken')->with(...$parameters);

        $decoratedService->expireUserToken(...$parameters);
    }

    public function testAssignUserToUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $serviceMock->expects($this->once())->method('assignUserToUserGroup')->with(...$parameters);

        $decoratedService->assignUserToUserGroup(...$parameters);
    }

    public function testUnAssignUserFromUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $serviceMock->expects($this->once())->method('unAssignUserFromUserGroup')->with(...$parameters);

        $decoratedService->unAssignUserFromUserGroup(...$parameters);
    }

    public function testLoadUserGroupsOfUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            'random_value_5ced05ce177f43.25078178',
            'random_value_5ced05ce177f51.93852014',
            ['random_value_5ced05ce177f66.49237325'],
        ];

        $serviceMock->expects($this->once())->method('loadUserGroupsOfUser')->with(...$parameters);

        $decoratedService->loadUserGroupsOfUser(...$parameters);
    }

    public function testLoadUsersOfUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(UserGroup::class),
            'random_value_5ced05ce177fa7.34344515',
            'random_value_5ced05ce177fb3.61754448',
            ['random_value_5ced05ce177fc8.32448790'],
        ];

        $serviceMock->expects($this->once())->method('loadUsersOfUserGroup')->with(...$parameters);

        $decoratedService->loadUsersOfUserGroup(...$parameters);
    }

    public function testIsUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Content::class)];

        $serviceMock->expects($this->once())->method('isUser')->with(...$parameters);

        $decoratedService->isUser(...$parameters);
    }

    public function testIsUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Content::class)];

        $serviceMock->expects($this->once())->method('isUserGroup')->with(...$parameters);

        $decoratedService->isUserGroup(...$parameters);
    }

    public function testNewUserCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce178030.00801248',
            'random_value_5ced05ce178049.06911955',
            'random_value_5ced05ce178050.58319472',
            'random_value_5ced05ce178063.84822784',
            'random_value_5ced05ce178075.03166061',
        ];

        $serviceMock->expects($this->once())->method('newUserCreateStruct')->with(...$parameters);

        $decoratedService->newUserCreateStruct(...$parameters);
    }

    public function testNewUserGroupCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce178098.19193304',
            'random_value_5ced05ce1780a5.92966105',
        ];

        $serviceMock->expects($this->once())->method('newUserGroupCreateStruct')->with(...$parameters);

        $decoratedService->newUserGroupCreateStruct(...$parameters);
    }

    public function testNewUserUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newUserUpdateStruct')->with(...$parameters);

        $decoratedService->newUserUpdateStruct(...$parameters);
    }

    public function testNewUserGroupUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newUserGroupUpdateStruct')->with(...$parameters);

        $decoratedService->newUserGroupUpdateStruct(...$parameters);
    }

    public function testValidatePasswordDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce1780f2.97072127',
            $this->createMock(PasswordValidationContext::class),
        ];

        $serviceMock->expects($this->once())->method('validatePassword')->with(...$parameters);

        $decoratedService->validatePassword(...$parameters);
    }
}
