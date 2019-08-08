<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\RoleTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Repository\Helper\RoleDomainMapper;
use eZ\Publish\Core\Repository\RoleService;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\User as SPIUser;
use eZ\Publish\SPI\Persistence\User\Role as SPIRole;
use eZ\Publish\SPI\Limitation\Type as SPIType;

/**
 * Mock test case for Role service.
 */
class RoleTest extends BaseServiceMockTest
{
    /**
     * Test for the createRole() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::createRole
     * @covers \eZ\Publish\Core\Repository\RoleService::validateRoleCreateStruct
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitations
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testCreateRoleThrowsLimitationValidationException()
    {
        $limitationMock = $this->createMock(RoleLimitation::class);
        $limitationTypeMock = $this->createMock(SPIType::class);

        $limitationMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('mockIdentifier'));

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue([42]));

        $settings = [
            'policyMap' => ['mockModule' => ['mockFunction' => ['mockIdentifier' => true]]],
            'limitationTypes' => ['mockIdentifier' => $limitationTypeMock],
        ];

        $roleServiceMock = $this->getPartlyMockedRoleService(['loadRoleByIdentifier'], $settings);

        /** @var \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStructMock */
        $roleCreateStructMock = $this->createMock(RoleCreateStruct::class);
        $policyCreateStructMock = $this->createMock(PolicyCreateStruct::class);

        /* @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStructMock */
        $policyCreateStructMock->module = 'mockModule';
        $policyCreateStructMock->function = 'mockFunction';
        $roleCreateStructMock->identifier = 'mockIdentifier';
        $roleServiceMock->expects($this->once())
            ->method('loadRoleByIdentifier')
            ->with($this->equalTo('mockIdentifier'))
            ->will($this->throwException(new NotFoundException('Role', 'mockIdentifier')));

        /* @var \PHPUnit\Framework\MockObject\MockObject $roleCreateStructMock */
        $roleCreateStructMock->expects($this->once())
            ->method('getPolicies')
            ->will($this->returnValue([$policyCreateStructMock]));

        /* @var \PHPUnit\Framework\MockObject\MockObject $policyCreateStructMock */
        $policyCreateStructMock->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue([$limitationMock]));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('create'),
                $this->equalTo($roleCreateStructMock)
            )->will($this->returnValue(true));

        /* @var \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStructMock */
        $roleServiceMock->createRole($roleCreateStructMock);
    }

    /**
     * Test for the addPolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::addPolicy
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitations
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testAddPolicyThrowsLimitationValidationException()
    {
        $limitationMock = $this->createMock(RoleLimitation::class);
        $limitationTypeMock = $this->createMock(SPIType::class);

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue([42]));

        $limitationMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('mockIdentifier'));

        $settings = [
            'policyMap' => ['mockModule' => ['mockFunction' => ['mockIdentifier' => true]]],
            'limitationTypes' => ['mockIdentifier' => $limitationTypeMock],
        ];

        $roleServiceMock = $this->getPartlyMockedRoleService(['loadRole'], $settings);

        $roleMock = $this->createMock(Role::class);
        $policyCreateStructMock = $this->createMock(PolicyCreateStruct::class);

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));
        /* @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStructMock */
        $policyCreateStructMock->module = 'mockModule';
        $policyCreateStructMock->function = 'mockFunction';

        $roleServiceMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue($roleMock));

        /* @var \PHPUnit\Framework\MockObject\MockObject $policyCreateStructMock */
        $policyCreateStructMock->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue([$limitationMock]));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update'),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        /* @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /* @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStructMock */
        $roleServiceMock->addPolicy($roleMock, $policyCreateStructMock);
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::updatePolicy
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitations
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testUpdatePolicyThrowsLimitationValidationException()
    {
        $limitationMock = $this->createMock(RoleLimitation::class);
        $limitationTypeMock = $this->createMock(SPIType::class);

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue([42]));

        $limitationMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('mockIdentifier'));

        $settings = [
            'policyMap' => ['mockModule' => ['mockFunction' => ['mockIdentifier' => true]]],
            'limitationTypes' => ['mockIdentifier' => $limitationTypeMock],
        ];

        $roleServiceMock = $this->getPartlyMockedRoleService(['loadRole'], $settings);

        $policyMock = $this->createMock(Policy::class);
        $policyUpdateStructMock = $this->createMock(PolicyUpdateStruct::class);

        $policyMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnCallback(
                    function ($propertyName) {
                        switch ($propertyName) {
                            case 'module':
                                return 'mockModule';
                            case 'function':
                                return 'mockFunction';
                        }

                        return null;
                    }
                )
            );

        /* @var \PHPUnit\Framework\MockObject\MockObject $policyCreateStructMock */
        $policyUpdateStructMock->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue([$limitationMock]));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update'),
                $this->equalTo($policyMock)
            )->will($this->returnValue(true));

        /* @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        /* @var \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStructMock */
        $roleServiceMock->updatePolicy($policyMock, $policyUpdateStructMock);
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUser
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserThrowsUnauthorizedException()
    {
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock(Role::class);
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->createMock(User::class);

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(false));

        $roleServiceMock->assignRoleToUser($roleMock, $userMock, null);
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testAssignRoleToUserThrowsLimitationValidationException()
    {
        $limitationMock = $this->createMock(RoleLimitation::class);
        $limitationTypeMock = $this->createMock(SPIType::class);

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue([42]));

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = [
            'limitationTypes' => ['testIdentifier' => $limitationTypeMock],
        ];

        $roleServiceMock = $this->getPartlyMockedRoleService(null, $settings);

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock(Role::class);
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->createMock(User::class);

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        /* @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUser($roleMock, $userMock, $limitationMock);
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testAssignRoleToUserThrowsBadStateException()
    {
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock(Role::class);
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->createMock(User::class);
        $limitationMock = $this->createMock(RoleLimitation::class);

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        /* @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUser($roleMock, $userMock, $limitationMock);
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     */
    public function testAssignRoleToUser()
    {
        $limitationMock = $this->createMock(RoleLimitation::class);
        $limitationTypeMock = $this->createMock(SPIType::class);

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue([]));

        $limitationMock->expects($this->exactly(2))
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = [
            'limitationTypes' => ['testIdentifier' => $limitationTypeMock],
        ];

        $roleServiceMock = $this->getPartlyMockedRoleService(['checkAssignmentAndFilterLimitationValues'], $settings);

        $repository = $this->getRepositoryMock();
        $roleMock = $this->createMock(Role::class);
        $userMock = $this->createMock(User::class);
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(['id' => 42])));

        $userHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(24))
            ->will($this->returnValue(new SPIUser(['id' => 24])));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf(SPIRole::class), ['testIdentifier' => []])
            ->will($this->returnValue(['testIdentifier' => []]));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(['testIdentifier' => []])
            );
        $repository->expects($this->once())->method('commit');

        /* @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /* @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        /* @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUser($roleMock, $userMock, $limitationMock);
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     */
    public function testAssignRoleToUserWithNullLimitation()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService(['checkAssignmentAndFilterLimitationValues']);
        $roleMock = $this->createMock(Role::class);
        $userMock = $this->createMock(User::class);
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(['id' => 42])));

        $userHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(24))
            ->will($this->returnValue(new SPIUser(['id' => 24])));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf(SPIRole::class), null)
            ->will($this->returnValue(null));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(null)
            );
        $repository->expects($this->once())->method('commit');

        /* @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /* @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $roleServiceMock->assignRoleToUser($roleMock, $userMock, null);
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \Exception
     */
    public function testAssignRoleToUserWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService(['checkAssignmentAndFilterLimitationValues']);
        $roleMock = $this->createMock(Role::class);
        $userMock = $this->createMock(User::class);
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(['id' => 42])));

        $userHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(24))
            ->will($this->returnValue(new SPIUser(['id' => 24])));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf(SPIRole::class), null)
            ->will($this->returnValue(null));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(null)
            )->will($this->throwException(new \Exception()));
        $repository->expects($this->once())->method('rollback');

        /* @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /* @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $roleServiceMock->assignRoleToUser($roleMock, $userMock, null);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock(Role::class);
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->createMock(UserGroup::class);

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(false));

        $roleServiceMock->assignRoleToUserGroup($roleMock, $userGroupMock, null);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testAssignRoleToUserGroupThrowsLimitationValidationException()
    {
        $limitationMock = $this->createMock(RoleLimitation::class);
        $limitationTypeMock = $this->createMock(SPIType::class);

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue([42]));

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = [
            'limitationTypes' => ['testIdentifier' => $limitationTypeMock],
        ];

        $roleServiceMock = $this->getPartlyMockedRoleService(null, $settings);

        $repository = $this->getRepositoryMock();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock(Role::class);
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->createMock(UserGroup::class);

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        /* @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUserGroup($roleMock, $userGroupMock, $limitationMock);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testAssignRoleGroupToUserThrowsBadStateException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock(Role::class);
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->createMock(UserGroup::class);
        $limitationMock = $this->createMock(RoleLimitation::class);

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        /* @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUserGroup($roleMock, $userGroupMock, $limitationMock);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     */
    public function testAssignRoleToUserGroup()
    {
        $limitationMock = $this->createMock(RoleLimitation::class);
        $limitationTypeMock = $this->createMock(SPIType::class);

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue([]));

        $limitationMock->expects($this->exactly(2))
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = [
            'limitationTypes' => ['testIdentifier' => $limitationTypeMock],
        ];

        $roleServiceMock = $this->getPartlyMockedRoleService(['checkAssignmentAndFilterLimitationValues'], $settings);

        $repository = $this->getRepositoryMock();
        $roleMock = $this->createMock(Role::class);
        $userGroupMock = $this->createMock(UserGroup::class);
        $userServiceMock = $this->createMock(UserService::class);
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $repository->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userServiceMock));
        $userGroupMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(['id' => 42])));

        $userServiceMock->expects($this->once())
            ->method('loadUserGroup')
            ->with($this->equalTo(24))
            ->will($this->returnValue($userGroupMock));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf(SPIRole::class), ['testIdentifier' => []])
            ->will($this->returnValue(['testIdentifier' => []]));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(['testIdentifier' => []])
            );
        $repository->expects($this->once())->method('commit');

        /* @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /* @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        /* @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUserGroup($roleMock, $userGroupMock, $limitationMock);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     */
    public function testAssignRoleToUserGroupWithNullLimitation()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService(['checkAssignmentAndFilterLimitationValues']);
        $roleMock = $this->createMock(Role::class);
        $userGroupMock = $this->createMock(UserGroup::class);
        $userServiceMock = $this->createMock(UserService::class);
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $repository->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userServiceMock));
        $userGroupMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(['id' => 42])));

        $userServiceMock->expects($this->once())
            ->method('loadUserGroup')
            ->with($this->equalTo(24))
            ->will($this->returnValue($userGroupMock));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf(SPIRole::class), null)
            ->will($this->returnValue(null));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(null)
            );
        $repository->expects($this->once())->method('commit');

        /* @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /* @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $roleServiceMock->assignRoleToUserGroup($roleMock, $userGroupMock, null);
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Helper\LimitationService::validateLimitation
     * @expectedException \Exception
     */
    public function testAssignRoleToUserGroupWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService(['checkAssignmentAndFilterLimitationValues']);
        $roleMock = $this->createMock(Role::class);
        $userGroupMock = $this->createMock(UserGroup::class);
        $userServiceMock = $this->createMock(UserService::class);
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $repository->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userServiceMock));
        $userGroupMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo([$roleMock])
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(['id' => 42])));

        $userServiceMock->expects($this->once())
            ->method('loadUserGroup')
            ->with($this->equalTo(24))
            ->will($this->returnValue($userGroupMock));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf(SPIRole::class), null)
            ->will($this->returnValue(null));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(null)
            )->will($this->throwException(new \Exception()));
        $repository->expects($this->once())->method('rollback');

        /* @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /* @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $roleServiceMock->assignRoleToUserGroup($roleMock, $userGroupMock, null);
    }

    /**
     * Test for the deletePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::deletePolicy
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeletePolicyThrowsUnauthorizedException()
    {
        $roleMock = $this->createMock(Role::class);
        $roleDomainMapper = $this->createMock(RoleDomainMapper::class);
        $roleDomainMapper
            ->method('buildDomainRoleObject')
            ->willReturn($roleMock);

        $roleServiceMock = $this->getPartlyMockedRoleService(['loadRole'], [], $roleDomainMapper);
        $policyMock = $this->createMock(Policy::class);
        $policyMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['roleId', 17],
                    ]
                )
            );

        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(17))
            ->will($this->returnValue(new SPIRole(['id' => 17])));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update'),
                $this->equalTo($roleMock)
            )->will($this->returnValue(false));

        /* @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->deletePolicy($policyMock);
    }

    /**
     * Test for the deletePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::deletePolicy
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testDeletePolicyWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $roleMock = $this->createMock(Role::class);
        $roleDomainMapper = $this->createMock(RoleDomainMapper::class);
        $roleDomainMapper
            ->method('buildDomainRoleObject')
            ->willReturn($roleMock);
        $roleServiceMock = $this->getPartlyMockedRoleService(null, [], $roleDomainMapper);

        $policyMock = $this->createMock(Policy::class);
        $policyMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', 42],
                        ['roleId', 17],
                    ]
                )
            );

        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(17))
            ->will($this->returnValue(new SPIRole(['id' => 17])));

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update'),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $repository->expects($this->once())->method('beginTransaction');

        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userHandlerMock->expects($this->once())
            ->method('deletePolicy')
            ->with(
                $this->equalTo(42)
            )->will($this->throwException(new \Exception('Handler threw an exception')));

        $repository->expects($this->once())->method('rollback');

        /* @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->deletePolicy($policyMock);
    }

    /**
     * Test for the deletePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\RoleService::deletePolicy
     */
    public function testDeletePolicy() /// FIX THIS
    {
        $repository = $this->getRepositoryMock();
        $roleMock = $this->createMock(Role::class);
        $roleDomainMapper = $this->createMock(RoleDomainMapper::class);
        $roleDomainMapper
            ->method('buildDomainRoleObject')
            ->willReturn($roleMock);

        $roleServiceMock = $this->getPartlyMockedRoleService(null, [], $roleDomainMapper);

        $policyMock = $this->createMock(Policy::class);
        $policyMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', 42],
                        ['roleId', 17],
                    ]
                )
            );

        $permissionResolverMock = $this->getPermissionResolverMock();
        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update'),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $repository->expects($this->once())->method('beginTransaction');

        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userHandlerMock->expects($this->once())
            ->method('deletePolicy')
            ->with(
                $this->equalTo(42)
            );

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(17))
            ->will($this->returnValue(new SPIRole(['id' => 17])));

        $repository->expects($this->once())->method('commit');

        /* @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->deletePolicy($policyMock);
    }

    /** @var \eZ\Publish\Core\Repository\RoleService */
    protected $partlyMockedRoleService;

    /**
     * Returns the role service to test with $methods mocked.
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     * @param array $settings
     * @param \eZ\Publish\Core\Repository\Helper\RoleDomainMapper|null $roleDomainMapper
     *
     * @return \eZ\Publish\Core\Repository\RoleService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPartlyMockedRoleService(
        array $methods = null,
        array $settings = [],
        ?RoleDomainMapper $roleDomainMapper = null
    ) {
        if (!isset($this->partlyMockedRoleService) || !empty($settings) || $roleDomainMapper) {
            $limitationService = $this->getPartlyMockedLimitationService($methods, $settings);
            if ($roleDomainMapper === null) {
                $roleDomainMapper = $this->getMockBuilder(RoleDomainMapper::class)
                    ->setMethods([])
                    ->setConstructorArgs([$limitationService])
                    ->getMock();
            }

            $this->partlyMockedRoleService = $this->getMockBuilder(RoleService::class)
                ->setMethods($methods)
                ->setConstructorArgs(
                    [
                        $this->getRepositoryMock(),
                        $this->getPersistenceMockHandler('User\\Handler'),
                        $limitationService,
                        $roleDomainMapper,
                        $settings,
                    ]
                )
                ->getMock();
        }

        return $this->partlyMockedRoleService;
    }

    /** @var \eZ\Publish\Core\Repository\RoleService */
    protected $partlyMockedLimitationService;

    /**
     * Return mocked LimitationService.
     *
     * @param string[] $methods
     * @param array $settings
     *
     * @return \eZ\Publish\Core\Repository\Helper\LimitationService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPartlyMockedLimitationService(array $methods = null, array $settings = [])
    {
        if (!isset($this->partlyMockedLimitationService) || !empty($settings)) {
            $this->partlyMockedLimitationService = $this->getMockBuilder('eZ\\Publish\\Core\\Repository\\Helper\\LimitationService')
                ->setMethods($methods)
                ->setConstructorArgs(
                    [
                        $settings,
                    ]
                )
                ->getMock();
        }

        return $this->partlyMockedLimitationService;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRepositoryMock(): Repository
    {
        $repositoryMock = parent::getRepositoryMock();
        $repositoryMock
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->willReturn($this->getPermissionResolverMock());

        return $repositoryMock;
    }
}
