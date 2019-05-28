<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Policy;
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
    protected function createDecorator(RoleService $service): RoleService
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

        $serviceMock->expects($this->exactly(1))->method('createRole')->with(...$parameters);

        $decoratedService->createRole(...$parameters);
    }

    public function testCreateRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Role::class)];

        $serviceMock->expects($this->exactly(1))->method('createRoleDraft')->with(...$parameters);

        $decoratedService->createRoleDraft(...$parameters);
    }

    public function testLoadRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce149001.67591733'];

        $serviceMock->expects($this->exactly(1))->method('loadRoleDraft')->with(...$parameters);

        $decoratedService->loadRoleDraft(...$parameters);
    }

    public function testLoadRoleDraftByRoleIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce149054.31531435'];

        $serviceMock->expects($this->exactly(1))->method('loadRoleDraftByRoleId')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('updateRoleDraft')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('addPolicyByRoleDraft')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('removePolicyByRoleDraft')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('updatePolicyByRoleDraft')->with(...$parameters);

        $decoratedService->updatePolicyByRoleDraft(...$parameters);
    }

    public function testDeleteRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(RoleDraft::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteRoleDraft')->with(...$parameters);

        $decoratedService->deleteRoleDraft(...$parameters);
    }

    public function testPublishRoleDraftDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(RoleDraft::class)];

        $serviceMock->expects($this->exactly(1))->method('publishRoleDraft')->with(...$parameters);

        $decoratedService->publishRoleDraft(...$parameters);
    }

    public function testUpdateRoleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('updateRole')->with(...$parameters);

        $decoratedService->updateRole(...$parameters);
    }

    public function testAddPolicyDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('addPolicy')->with(...$parameters);

        $decoratedService->addPolicy(...$parameters);
    }

    public function testDeletePolicyDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Policy::class)];

        $serviceMock->expects($this->exactly(1))->method('deletePolicy')->with(...$parameters);

        $decoratedService->deletePolicy(...$parameters);
    }

    public function testUpdatePolicyDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Policy::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('updatePolicy')->with(...$parameters);

        $decoratedService->updatePolicy(...$parameters);
    }

    public function testLoadRoleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14b703.67687595'];

        $serviceMock->expects($this->exactly(1))->method('loadRole')->with(...$parameters);

        $decoratedService->loadRole(...$parameters);
    }

    public function testLoadRoleByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14b742.13672543'];

        $serviceMock->expects($this->exactly(1))->method('loadRoleByIdentifier')->with(...$parameters);

        $decoratedService->loadRoleByIdentifier(...$parameters);
    }

    public function testLoadRolesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('loadRoles')->with(...$parameters);

        $decoratedService->loadRoles(...$parameters);
    }

    public function testDeleteRoleDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Role::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteRole')->with(...$parameters);

        $decoratedService->deleteRole(...$parameters);
    }

    public function testLoadPoliciesByUserIdDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14b7b9.92276046'];

        $serviceMock->expects($this->exactly(1))->method('loadPoliciesByUserId')->with(...$parameters);

        $decoratedService->loadPoliciesByUserId(...$parameters);
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

        $serviceMock->expects($this->exactly(1))->method('assignRoleToUserGroup')->with(...$parameters);

        $decoratedService->assignRoleToUserGroup(...$parameters);
    }

    public function testUnassignRoleFromUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('unassignRoleFromUserGroup')->with(...$parameters);

        $decoratedService->unassignRoleFromUserGroup(...$parameters);
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

        $serviceMock->expects($this->exactly(1))->method('assignRoleToUser')->with(...$parameters);

        $decoratedService->assignRoleToUser(...$parameters);
    }

    public function testUnassignRoleFromUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('unassignRoleFromUser')->with(...$parameters);

        $decoratedService->unassignRoleFromUser(...$parameters);
    }

    public function testLoadRoleAssignmentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14cfc5.25468747'];

        $serviceMock->expects($this->exactly(1))->method('loadRoleAssignment')->with(...$parameters);

        $decoratedService->loadRoleAssignment(...$parameters);
    }

    public function testGetRoleAssignmentsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Role::class)];

        $serviceMock->expects($this->exactly(1))->method('getRoleAssignments')->with(...$parameters);

        $decoratedService->getRoleAssignments(...$parameters);
    }

    public function testGetRoleAssignmentsForUserDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(User::class),
            'random_value_5ced05ce14d033.64536244',
        ];

        $serviceMock->expects($this->exactly(1))->method('getRoleAssignmentsForUser')->with(...$parameters);

        $decoratedService->getRoleAssignmentsForUser(...$parameters);
    }

    public function testGetRoleAssignmentsForUserGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(UserGroup::class)];

        $serviceMock->expects($this->exactly(1))->method('getRoleAssignmentsForUserGroup')->with(...$parameters);

        $decoratedService->getRoleAssignmentsForUserGroup(...$parameters);
    }

    public function testRemoveRoleAssignmentDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(RoleAssignment::class)];

        $serviceMock->expects($this->exactly(1))->method('removeRoleAssignment')->with(...$parameters);

        $decoratedService->removeRoleAssignment(...$parameters);
    }

    public function testNewRoleCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14d674.30093215'];

        $serviceMock->expects($this->exactly(1))->method('newRoleCreateStruct')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('newPolicyCreateStruct')->with(...$parameters);

        $decoratedService->newPolicyCreateStruct(...$parameters);
    }

    public function testNewPolicyUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newPolicyUpdateStruct')->with(...$parameters);

        $decoratedService->newPolicyUpdateStruct(...$parameters);
    }

    public function testNewRoleUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newRoleUpdateStruct')->with(...$parameters);

        $decoratedService->newRoleUpdateStruct(...$parameters);
    }

    public function testGetLimitationTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce14d714.69905914'];

        $serviceMock->expects($this->exactly(1))->method('getLimitationType')->with(...$parameters);

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

        $serviceMock->expects($this->exactly(1))->method('getLimitationTypesByModuleFunction')->with(...$parameters);

        $decoratedService->getLimitationTypesByModuleFunction(...$parameters);
    }
}
