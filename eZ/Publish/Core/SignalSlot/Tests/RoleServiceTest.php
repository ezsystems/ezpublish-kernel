<?php

/**
 * File containing the RoleTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

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

class RoleServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\RoleService'
        );
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
            array(
                'id' => $roleId,
                'identifier' => $roleIdentifier,
            )
        );
        $roleDraft = new RoleDraft(['innerRole' => $role]);
        $policy = new Policy(
            array(
                'id' => $policyId,
                'roleId' => $roleId,
            )
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
            array(
                'user' => $user,
                'role' => $role,
            )
        );
        $userGroupRoleAssignement = new UserGroupRoleAssignment(
            array(
                'userGroup' => $userGroup,
                'role' => $role,
            )
        );

        return array(
            array(
                'createRole',
                array($roleCreateStruct),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\CreateRoleSignal',
                array('roleId' => $roleId),
            ),
            array(
                'createRoleDraft',
                array($role),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\CreateRoleDraftSignal',
                array('roleId' => $roleId),
            ),
            array(
                'updateRole',
                array($role, $roleUpdateStruct),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdateRoleSignal',
                array('roleId' => $roleId),
            ),
            array(
                'updateRoleDraft',
                array($roleDraft, $roleUpdateStruct),
                $roleDraft,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdateRoleDraftSignal',
                array('roleId' => $roleId),
            ),
            array(
                'publishRoleDraft',
                array($roleDraft),
                $roleDraft,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\PublishRoleDraftSignal',
                array('roleId' => $roleId),
            ),
            array(
                'addPolicy',
                array($role, $policyCreateStruct),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\AddPolicySignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $roleId,
                ),
            ),
            array(
                'addPolicyByRoleDraft',
                array($roleDraft, $policyCreateStruct),
                $roleDraft,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\AddPolicyByRoleDraftSignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $roleId,
                ),
            ),
            array(
                'removePolicy',
                array($role, $policy),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicySignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $policyId,
                ),
            ),
            array(
                'removePolicyByRoleDraft',
                array($roleDraft, $policyDraft),
                $roleDraft,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicyByRoleDraftSignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $policyId,
                ),
            ),
            array(
                'deletePolicy',
                array($policy),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicySignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $policyId,
                ),
            ),
            array(
                'updatePolicy',
                array($policy, $policyUpdateStruct),
                $policy,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdatePolicySignal',
                array('policyId' => $policyId),
            ),
            array(
                'loadRole',
                array($roleId),
                $role,
                0,
            ),
            array(
                'loadRoleDraft',
                array($roleId),
                $roleDraft,
                0,
            ),
            array(
                'loadRoleByIdentifier',
                array($roleIdentifier),
                $role,
                0,
            ),
            array(
                'loadRoles',
                array(),
                array($role),
                0,
            ),
            array(
                'deleteRole',
                array($role),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\DeleteRoleSignal',
                array('roleId' => $roleId),
            ),
            array(
                'deleteRoleDraft',
                array($roleDraft),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\DeleteRoleDraftSignal',
                array('roleId' => $roleId),
            ),
            array(
                'loadPoliciesByUserId',
                array($userId),
                array($policy),
                0,
            ),
            array(
                'assignRoleToUserGroup',
                array($role, $userGroup, $roleLimitation),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserGroupSignal',
                array(
                    'roleId' => $roleId,
                    'userGroupId' => $userGroupId,
                    'roleLimitation' => $roleLimitation,
                ),
            ),
            array(
                'unassignRoleFromUserGroup',
                array($role, $userGroup),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserGroupSignal',
                array(
                    'roleId' => $roleId,
                    'userGroupId' => $userGroupId,
                ),
            ),
            array(
                'assignRoleToUser',
                array($role, $user, $roleLimitation),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserSignal',
                array(
                    'roleId' => $roleId,
                    'userId' => $userId,
                    'roleLimitation' => $roleLimitation,
                ),
            ),
            array(
                'unassignRoleFromUser',
                array($role, $user),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserSignal',
                array(
                    'roleId' => $roleId,
                    'userId' => $userId,
                ),
            ),
            array(
                'getRoleAssignments',
                array($role),
                array($roleAssignement),
                0,
            ),
            array(
                'getRoleAssignmentsForUser',
                array($user, true),
                array($roleAssignement),
                0,
            ),
            array(
                'getRoleAssignmentsForUserGroup',
                array($userGroup),
                array($userGroupRoleAssignement),
                0,
            ),
            array(
                'newRoleCreateStruct',
                array('new role name'),
                $roleCreateStruct,
                0,
            ),
            array(
                'newPolicyCreateStruct',
                array('section', 'view'),
                $policyCreateStruct,
                0,
            ),
            array(
                'newPolicyUpdateStruct',
                array(),
                $policyUpdateStruct,
                0,
            ),
            array(
                'newRoleUpdateStruct',
                array(),
                $roleUpdateStruct,
                0,
            ),
            array(
                'getLimitationType',
                array('identifier'),
                $this->getMock('eZ\\Publish\\SPI\\Limitation\\Type'),
                0,
            ),
            array(
                'getLimitationTypesByModuleFunction',
                array('module', 'function'),
                array($this->getMock('eZ\\Publish\\SPI\\Limitation\\Type')),
                0,
            ),
        );
    }
}
