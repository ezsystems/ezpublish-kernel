<?php

/**
 * File containing the RoleTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\RoleService as APIRoleService;
use eZ\Publish\Core\Repository\Values\User\PolicyDraft;
use eZ\Publish\Core\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\Core\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\RoleService;
use eZ\Publish\SPI\Limitation\Type as LimitationType;
use eZ\Publish\Core\SignalSlot\Signal\RoleService as RoleServiceSignals;

class RoleServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIRoleService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new RoleService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $roleId = 3;
        $roleIdentifier = 'role_identifier';
        $policyId = 42;
        $userId = 14;
        $userGroupId = 25;

        $role = new Role(
            [
                'id' => $roleId,
                'identifier' => $roleIdentifier,
            ]
        );
        $roleDraft = new RoleDraft(['innerRole' => $role]);
        $policy = new Policy(
            [
                'id' => $policyId,
                'roleId' => $roleId,
            ]
        );
        $policyDraft = new PolicyDraft(['innerPolicy' => $policy]);
        $roleCreateStruct = new RoleCreateStruct();
        $roleUpdateStruct = new RoleUpdateStruct();
        $policyCreateStruct = new PolicyCreateStruct();
        $policyUpdateStruct = new PolicyUpdateStruct();

        $userGroup = $this->getUserGroup($userGroupId, md5('user group'), 3);
        $roleLimitation = new SectionLimitation();

        $user = $this->getUser($userId, md5('user'), 4);
        $roleAssignement = new UserRoleAssignment(
            [
                'user' => $user,
                'role' => $role,
            ]
        );
        $userGroupRoleAssignement = new UserGroupRoleAssignment(
            [
                'userGroup' => $userGroup,
                'role' => $role,
            ]
        );

        return [
            [
                'createRole',
                [$roleCreateStruct],
                $role,
                1,
                RoleServiceSignals\CreateRoleSignal::class,
                ['roleId' => $roleId],
            ],
            [
                'createRoleDraft',
                [$role],
                $role,
                1,
                RoleServiceSignals\CreateRoleDraftSignal::class,
                ['roleId' => $roleId],
            ],
            [
                'updateRole',
                [$role, $roleUpdateStruct],
                $role,
                1,
                RoleServiceSignals\UpdateRoleSignal::class,
                ['roleId' => $roleId],
            ],
            [
                'updateRoleDraft',
                [$roleDraft, $roleUpdateStruct],
                $roleDraft,
                1,
                RoleServiceSignals\UpdateRoleDraftSignal::class,
                ['roleId' => $roleId],
            ],
            [
                'publishRoleDraft',
                [$roleDraft],
                $roleDraft,
                1,
                RoleServiceSignals\PublishRoleDraftSignal::class,
                ['roleId' => $roleId],
            ],
            [
                'addPolicy',
                [$role, $policyCreateStruct],
                $role,
                1,
                RoleServiceSignals\AddPolicySignal::class,
                [
                    'roleId' => $roleId,
                    'policyId' => $roleId,
                ],
            ],
            [
                'addPolicyByRoleDraft',
                [$roleDraft, $policyCreateStruct],
                $roleDraft,
                1,
                RoleServiceSignals\AddPolicyByRoleDraftSignal::class,
                [
                    'roleId' => $roleId,
                    'policyId' => $roleId,
                ],
            ],
            [
                'removePolicyByRoleDraft',
                [$roleDraft, $policyDraft],
                $roleDraft,
                1,
                RoleServiceSignals\RemovePolicyByRoleDraftSignal::class,
                [
                    'roleId' => $roleId,
                    'policyId' => $policyId,
                ],
            ],
            [
                'deletePolicy',
                [$policy],
                null,
                1,
                RoleServiceSignals\RemovePolicySignal::class,
                [
                    'roleId' => $roleId,
                    'policyId' => $policyId,
                ],
            ],
            [
                'updatePolicy',
                [$policy, $policyUpdateStruct],
                $policy,
                1,
                RoleServiceSignals\UpdatePolicySignal::class,
                ['policyId' => $policyId],
            ],
            [
                'loadRole',
                [$roleId],
                $role,
                0,
            ],
            [
                'loadRoleDraft',
                [$roleId],
                $roleDraft,
                0,
            ],
            [
                'loadRoleByIdentifier',
                [$roleIdentifier],
                $role,
                0,
            ],
            [
                'loadRoles',
                [],
                [$role],
                0,
            ],
            [
                'deleteRole',
                [$role],
                null,
                1,
                RoleServiceSignals\DeleteRoleSignal::class,
                ['roleId' => $roleId],
            ],
            [
                'deleteRoleDraft',
                [$roleDraft],
                null,
                1,
                RoleServiceSignals\DeleteRoleDraftSignal::class,
                ['roleId' => $roleId],
            ],
            [
                'loadPoliciesByUserId',
                [$userId],
                [$policy],
                0,
            ],
            [
                'assignRoleToUserGroup',
                [$role, $userGroup, $roleLimitation],
                null,
                1,
                RoleServiceSignals\AssignRoleToUserGroupSignal::class,
                [
                    'roleId' => $roleId,
                    'userGroupId' => $userGroupId,
                    'roleLimitation' => $roleLimitation,
                ],
            ],
            [
                'unassignRoleFromUserGroup',
                [$role, $userGroup],
                null,
                1,
                RoleServiceSignals\UnassignRoleFromUserGroupSignal::class,
                [
                    'roleId' => $roleId,
                    'userGroupId' => $userGroupId,
                ],
            ],
            [
                'assignRoleToUser',
                [$role, $user, $roleLimitation],
                null,
                1,
                RoleServiceSignals\AssignRoleToUserSignal::class,
                [
                    'roleId' => $roleId,
                    'userId' => $userId,
                    'roleLimitation' => $roleLimitation,
                ],
            ],
            [
                'unassignRoleFromUser',
                [$role, $user],
                null,
                1,
                RoleServiceSignals\UnassignRoleFromUserSignal::class,
                [
                    'roleId' => $roleId,
                    'userId' => $userId,
                ],
            ],
            [
                'getRoleAssignments',
                [$role],
                [$roleAssignement],
                0,
            ],
            [
                'getRoleAssignmentsForUser',
                [$user, true],
                [$roleAssignement],
                0,
            ],
            [
                'getRoleAssignmentsForUserGroup',
                [$userGroup],
                [$userGroupRoleAssignement],
                0,
            ],
            [
                'newRoleCreateStruct',
                ['new role name'],
                $roleCreateStruct,
                0,
            ],
            [
                'newPolicyCreateStruct',
                ['section', 'view'],
                $policyCreateStruct,
                0,
            ],
            [
                'newPolicyUpdateStruct',
                [],
                $policyUpdateStruct,
                0,
            ],
            [
                'newRoleUpdateStruct',
                [],
                $roleUpdateStruct,
                0,
            ],
            [
                'getLimitationType',
                ['identifier'],
                $this->createMock(LimitationType::class),
                0,
            ],
            [
                'getLimitationTypesByModuleFunction',
                ['module', 'function'],
                [$this->createMock(LimitationType::class)],
                0,
            ],
        ];
    }
}
