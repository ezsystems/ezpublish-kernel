<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\RoleTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\User as SPIUser;
use eZ\Publish\SPI\Persistence\User\Role as SPIRole;

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
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');

        $limitationMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('mockIdentifier'));

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue(array(42)));

        $settings = array(
            'policyMap' => array('mockModule' => array('mockFunction' => array('mockIdentifier' => true))),
            'limitationTypes' => array('mockIdentifier' => $limitationTypeMock),
        );

        $roleServiceMock = $this->getPartlyMockedRoleService(array('loadRoleByIdentifier'), $settings);

        $repository = $this->getRepositoryMock();
        /** @var \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStructMock */
        $roleCreateStructMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\RoleCreateStruct');
        $policyCreateStructMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct');

        /* @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStructMock */
        $policyCreateStructMock->module = 'mockModule';
        $policyCreateStructMock->function = 'mockFunction';
        $roleCreateStructMock->identifier = 'mockIdentifier';
        $roleServiceMock->expects($this->once())
            ->method('loadRoleByIdentifier')
            ->with($this->equalTo('mockIdentifier'))
            ->will($this->throwException(new NotFoundException('Role', 'mockIdentifier')));

        /* @var \PHPUnit_Framework_MockObject_MockObject $roleCreateStructMock */
        $roleCreateStructMock->expects($this->once())
            ->method('getPolicies')
            ->will($this->returnValue(array($policyCreateStructMock)));

        /* @var \PHPUnit_Framework_MockObject_MockObject $policyCreateStructMock */
        $policyCreateStructMock->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue(array($limitationMock)));

        $repository->expects($this->once())
            ->method('hasAccess')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('create')
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
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue(array(42)));

        $limitationMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('mockIdentifier'));

        $settings = array(
            'policyMap' => array('mockModule' => array('mockFunction' => array('mockIdentifier' => true))),
            'limitationTypes' => array('mockIdentifier' => $limitationTypeMock),
        );

        $roleServiceMock = $this->getPartlyMockedRoleService(array('loadRole'), $settings);

        $repository = $this->getRepositoryMock();
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        $policyCreateStructMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct');

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

        /* @var \PHPUnit_Framework_MockObject_MockObject $policyCreateStructMock */
        $policyCreateStructMock->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue(array($limitationMock)));

        $repository->expects($this->once())
            ->method('hasAccess')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update')
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
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue(array(42)));

        $limitationMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('mockIdentifier'));

        $settings = array(
            'policyMap' => array('mockModule' => array('mockFunction' => array('mockIdentifier' => true))),
            'limitationTypes' => array('mockIdentifier' => $limitationTypeMock),
        );

        $roleServiceMock = $this->getPartlyMockedRoleService(array('loadRole'), $settings);

        $repository = $this->getRepositoryMock();
        $policyMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Policy');
        $policyUpdateStructMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\PolicyUpdateStruct');

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

        /* @var \PHPUnit_Framework_MockObject_MockObject $policyCreateStructMock */
        $policyUpdateStructMock->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue(array($limitationMock)));

        $repository->expects($this->once())
            ->method('hasAccess')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update')
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
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo($roleMock)
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
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue(array(42)));

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = array(
            'limitationTypes' => array('testIdentifier' => $limitationTypeMock),
        );

        $roleServiceMock = $this->getPartlyMockedRoleService(null, $settings);

        $repository = $this->getRepositoryMock();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo($roleMock)
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
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo($roleMock)
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
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue(array()));

        $limitationMock->expects($this->exactly(2))
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = array(
            'limitationTypes' => array('testIdentifier' => $limitationTypeMock),
        );

        $roleServiceMock = $this->getPartlyMockedRoleService(array('checkAssignmentAndFilterLimitationValues'), $settings);

        $repository = $this->getRepositoryMock();
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        $userMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(array('id' => 42))));

        $userHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(24))
            ->will($this->returnValue(new SPIUser(array('id' => 24))));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\Role'), array('testIdentifier' => array()))
            ->will($this->returnValue(array('testIdentifier' => array())));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(array('testIdentifier' => array()))
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
        $roleServiceMock = $this->getPartlyMockedRoleService(array('checkAssignmentAndFilterLimitationValues'));
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        $userMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(array('id' => 42))));

        $userHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(24))
            ->will($this->returnValue(new SPIUser(array('id' => 24))));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\Role'), null)
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
        $roleServiceMock = $this->getPartlyMockedRoleService(array('checkAssignmentAndFilterLimitationValues'));
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        $userMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userMock),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(array('id' => 42))));

        $userHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(24))
            ->will($this->returnValue(new SPIUser(array('id' => 24))));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\Role'), null)
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
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup');

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo($roleMock)
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
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue(array(42)));

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = array(
            'limitationTypes' => array('testIdentifier' => $limitationTypeMock),
        );

        $roleServiceMock = $this->getPartlyMockedRoleService(null, $settings);

        $repository = $this->getRepositoryMock();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup');

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo($roleMock)
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
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup');
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');

        $limitationMock->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo($roleMock)
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
        $limitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation');
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');

        $limitationTypeMock->expects($this->once())
            ->method('acceptValue')
            ->with($this->equalTo($limitationMock));
        $limitationTypeMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($limitationMock))
            ->will($this->returnValue(array()));

        $limitationMock->expects($this->exactly(2))
            ->method('getIdentifier')
            ->will($this->returnValue('testIdentifier'));

        $settings = array(
            'limitationTypes' => array('testIdentifier' => $limitationTypeMock),
        );

        $roleServiceMock = $this->getPartlyMockedRoleService(array('checkAssignmentAndFilterLimitationValues'), $settings);

        $repository = $this->getRepositoryMock();
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        $userGroupMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup');
        $userServiceMock = $this->createMock('eZ\\Publish\\API\\Repository\\UserService');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $repository->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userServiceMock));
        $userGroupMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(array('id' => 42))));

        $userServiceMock->expects($this->once())
            ->method('loadUserGroup')
            ->with($this->equalTo(24))
            ->will($this->returnValue($userGroupMock));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\Role'), array('testIdentifier' => array()))
            ->will($this->returnValue(array('testIdentifier' => array())));

        $repository->expects($this->once())->method('beginTransaction');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');
        $userHandlerMock->expects($this->once())
            ->method('assignRole')
            ->with(
                $this->equalTo(24),
                $this->equalTo(42),
                $this->equalTo(array('testIdentifier' => array()))
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
        $roleServiceMock = $this->getPartlyMockedRoleService(array('checkAssignmentAndFilterLimitationValues'));
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        $userGroupMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup');
        $userServiceMock = $this->createMock('eZ\\Publish\\API\\Repository\\UserService');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $repository->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userServiceMock));
        $userGroupMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(array('id' => 42))));

        $userServiceMock->expects($this->once())
            ->method('loadUserGroup')
            ->with($this->equalTo(24))
            ->will($this->returnValue($userGroupMock));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\Role'), null)
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
        $roleServiceMock = $this->getPartlyMockedRoleService(array('checkAssignmentAndFilterLimitationValues'));
        $roleMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Role');
        $userGroupMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup');
        $userServiceMock = $this->createMock('eZ\\Publish\\API\\Repository\\UserService');
        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $repository->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userServiceMock));
        $userGroupMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(24));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('assign'),
                $this->equalTo($userGroupMock),
                $this->equalTo($roleMock)
            )->will($this->returnValue(true));

        $roleMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $userHandlerMock->expects($this->once())
            ->method('loadRole')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new SPIRole(array('id' => 42))));

        $userServiceMock->expects($this->once())
            ->method('loadUserGroup')
            ->with($this->equalTo(24))
            ->will($this->returnValue($userGroupMock));

        $roleServiceMock->expects($this->once())
            ->method('checkAssignmentAndFilterLimitationValues')
            ->with(24, $this->isInstanceOf('\\eZ\\Publish\\SPI\\Persistence\\User\\Role'), null)
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
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Policy');

        $repository->expects($this->once())
            ->method('hasAccess')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update')
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
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Policy');

        $policyMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    array(
                        array('id', 42),
                    )
                )
            );

        $repository->expects($this->once())
            ->method('hasAccess')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update')
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
    public function testDeletePolicy()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Policy');

        $policyMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    array(
                        array('id', 42),
                    )
                )
            );

        $repository->expects($this->once())
            ->method('hasAccess')
            ->with(
                $this->equalTo('role'),
                $this->equalTo('update')
            )->will($this->returnValue(true));

        $repository->expects($this->once())->method('beginTransaction');

        $userHandlerMock = $this->getPersistenceMockHandler('User\\Handler');

        $userHandlerMock->expects($this->once())
            ->method('deletePolicy')
            ->with(
                $this->equalTo(42)
            );

        $repository->expects($this->once())->method('commit');

        /* @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->deletePolicy($policyMock);
    }

    /**
     * @var \eZ\Publish\Core\Repository\RoleService
     */
    protected $partlyMockedRoleService;

    /**
     * Returns the role service to test with $methods mocked.
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     * @param array $settings
     *
     * @return \eZ\Publish\Core\Repository\RoleService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedRoleService(array $methods = null, array $settings = array())
    {
        if (!isset($this->partlyMockedRoleService) || !empty($settings)) {
            $this->partlyMockedRoleService = $this->getMockBuilder('eZ\\Publish\\Core\\Repository\\RoleService')
                ->setMethods($methods)
                ->setConstructorArgs(
                    array(
                        $this->getRepositoryMock(),
                        $this->getPersistenceMockHandler('User\\Handler'),
                        $limitationService = $this->getPartlyMockedLimitationService($methods, $settings),
                        $this->getMockBuilder('eZ\\Publish\\Core\\Repository\\Helper\\RoleDomainMapper')
                            ->setMethods(array())
                            ->setConstructorArgs(array($limitationService))
                            ->getMock(),
                        $settings,
                    )
                )
                ->getMock();
        }

        return $this->partlyMockedRoleService;
    }

    /**
     * @var \eZ\Publish\Core\Repository\RoleService
     */
    protected $partlyMockedLimitationService;

    /**
     * Return mocked LimitationService.
     *
     * @param string[] $methods
     * @param array $settings
     *
     * @return \eZ\Publish\Core\Repository\Helper\LimitationService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedLimitationService(array $methods = null, array $settings = array())
    {
        if (!isset($this->partlyMockedLimitationService) || !empty($settings)) {
            $this->partlyMockedLimitationService = $this->getMockBuilder('eZ\\Publish\\Core\\Repository\\Helper\\LimitationService')
                ->setMethods($methods)
                ->setConstructorArgs(
                    array(
                        $settings,
                    )
                )
                ->getMock();
        }

        return $this->partlyMockedLimitationService;
    }
}
