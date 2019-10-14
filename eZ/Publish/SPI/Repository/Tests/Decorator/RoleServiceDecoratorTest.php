<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Repository\Decorator\RoleServiceDecorator;

class RoleServiceDecoratorTest extends TestCase
{
    private const ROLE_ID = 1;
    private const ROLE_ASSIGNMENT_ID = 1;

    protected function createDecorator(MockObject $service): RoleService
    {
        return new class($service) extends RoleServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(RoleService::class);
    }

    public function testCreateRoleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(RoleCreateStruct::class)];

        $serviceMock->expects($this->once())->method('createRole')->with(...$parameters);

        $decoratedService->createRole(...$parameters);
    }

    public function testCreateRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Role::class)];

        $serviceMock->expects($this->once())->method('createRoleDraft')->with(...$parameters);

        $decoratedService->createRoleDraft(...$parameters);
    }

    public function testLoadRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [self::ROLE_ID];

        $serviceMock->expects($this->once())->method('loadRoleDraft')->with(...$parameters);

        $decoratedService->loadRoleDraft(...$parameters);
    }

    public function testLoadRoleDraftByRoleIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [self::ROLE_ID];

        $serviceMock->expects($this->once())->method('loadRoleDraftByRoleId')->with(...$parameters);

        $decoratedService->loadRoleDraftByRoleId(...$parameters);
    }

    public function testUpdateRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updateRoleDraft')->with(...$parameters);

        $decoratedService->updateRoleDraft(...$parameters);
    }

    public function testAddPolicyByRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('addPolicyByRoleDraft')->with(...$parameters);

        $decoratedService->addPolicyByRoleDraft(...$parameters);
    }

    public function testRemovePolicyByRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
        ];

        $serviceMock->expects($this->once())->method('removePolicyByRoleDraft')->with(...$parameters);

        $decoratedService->removePolicyByRoleDraft(...$parameters);
    }

    public function testUpdatePolicyByRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $serviceMock->expects($this->once())->method('updatePolicyByRoleDraft')->with(...$parameters);

        $decoratedService->updatePolicyByRoleDraft(...$parameters);
    }

    public function testDeleteRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(RoleDraft::class)];

        $serviceMock->expects($this->once())->method('deleteRoleDraft')->with(...$parameters);

        $decoratedService->deleteRoleDraft(...$parameters);
    }

    public function testPublishRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(RoleDraft::class)];

        $serviceMock->expects($this->once())->method('publishRoleDraft')->with(...$parameters);

        $decoratedService->publishRoleDraft(...$parameters);
    }

    public function testLoadRoleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [self::ROLE_ID];

        $serviceMock->expects($this->once())->method('loadRole')->with(...$parameters);

        $decoratedService->loadRole(...$parameters);
    }

    public function testLoadRoleByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14b742.13672543'];

        $serviceMock->expects($this->once())->method('loadRoleByIdentifier')->with(...$parameters);

        $decoratedService->loadRoleByIdentifier(...$parameters);
    }

    public function testLoadRolesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('loadRoles')->with(...$parameters);

        $decoratedService->loadRoles(...$parameters);
    }

    public function testDeleteRoleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Role::class)];

        $serviceMock->expects($this->once())->method('deleteRole')->with(...$parameters);

        $decoratedService->deleteRole(...$parameters);
    }

    public function testAssignRoleToUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
            $this->createMock(RoleLimitation::class),
        ];

        $serviceMock->expects($this->once())->method('assignRoleToUserGroup')->with(...$parameters);

        $decoratedService->assignRoleToUserGroup(...$parameters);
    }

    public function testAssignRoleToUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
            $this->createMock(RoleLimitation::class),
        ];

        $serviceMock->expects($this->once())->method('assignRoleToUser')->with(...$parameters);

        $decoratedService->assignRoleToUser(...$parameters);
    }

    public function testLoadRoleAssignmentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [self::ROLE_ASSIGNMENT_ID];

        $serviceMock->expects($this->once())->method('loadRoleAssignment')->with(...$parameters);

        $decoratedService->loadRoleAssignment(...$parameters);
    }

    public function testGetRoleAssignmentsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Role::class)];

        $serviceMock->expects($this->once())->method('getRoleAssignments')->with(...$parameters);

        $decoratedService->getRoleAssignments(...$parameters);
    }

    public function testGetRoleAssignmentsForUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            true,
        ];

        $serviceMock->expects($this->once())->method('getRoleAssignmentsForUser')->with(...$parameters);

        $decoratedService->getRoleAssignmentsForUser(...$parameters);
    }

    public function testGetRoleAssignmentsForUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(UserGroup::class)];

        $serviceMock->expects($this->once())->method('getRoleAssignmentsForUserGroup')->with(...$parameters);

        $decoratedService->getRoleAssignmentsForUserGroup(...$parameters);
    }

    public function testRemoveRoleAssignmentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(RoleAssignment::class)];

        $serviceMock->expects($this->once())->method('removeRoleAssignment')->with(...$parameters);

        $decoratedService->removeRoleAssignment(...$parameters);
    }

    public function testNewRoleCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14d674.30093215'];

        $serviceMock->expects($this->once())->method('newRoleCreateStruct')->with(...$parameters);

        $decoratedService->newRoleCreateStruct(...$parameters);
    }

    public function testNewPolicyCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce14d6a8.88877327',
            'random_value_5ced05ce14d6b6.22048821',
        ];

        $serviceMock->expects($this->once())->method('newPolicyCreateStruct')->with(...$parameters);

        $decoratedService->newPolicyCreateStruct(...$parameters);
    }

    public function testNewPolicyUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newPolicyUpdateStruct')->with(...$parameters);

        $decoratedService->newPolicyUpdateStruct(...$parameters);
    }

    public function testNewRoleUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newRoleUpdateStruct')->with(...$parameters);

        $decoratedService->newRoleUpdateStruct(...$parameters);
    }

    public function testGetLimitationTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14d714.69905914'];

        $serviceMock->expects($this->once())->method('getLimitationType')->with(...$parameters);

        $decoratedService->getLimitationType(...$parameters);
    }

    public function testGetLimitationTypesByModuleFunctionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce14d732.11207294',
            'random_value_5ced05ce14d743.90303575',
        ];

        $serviceMock->expects($this->once())->method('getLimitationTypesByModuleFunction')->with(...$parameters);

        $decoratedService->getLimitationTypesByModuleFunction(...$parameters);
    }
}
