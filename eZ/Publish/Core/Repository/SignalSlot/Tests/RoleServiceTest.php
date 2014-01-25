<?php
/**
 * File containing the RoleTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\DomainLogic\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\PolicyCreateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\PolicyUpdateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\Role;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\UserRoleAssignment;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\UserGroupRoleAssignment;

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

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new RoleService( $coreService, $dispatcher );
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
        $policy = new Policy(
            array(
                'id' => $policyId,
                'roleId' => $roleId
            )
        );
        $roleCreateStruct = new RoleCreateStruct();
        $roleUpdateStruct = new RoleUpdateStruct();
        $policyCreateStruct = new PolicyCreateStruct();
        $policyUpdateStruct = new PolicyUpdateStruct();

        $userGroup = $this->getUserGroup( $userGroupId, md5( 'user group' ), 3 );
        $roleLimitation = new SectionLimitation();

        $user = $this->getUser( $userId, md5( 'user' ), 4 );
        $roleAssignement = new UserRoleAssignment(
            array(
                'user' => $user,
                'role' => $role
            )
        );
        $userGroupRoleAssignement = new UserGroupRoleAssignment(
            array(
                'userGroup' => $userGroup,
                'role' => $role
            )
        );

        return array(
            array(
                'createRole',
                array( $roleCreateStruct ),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\CreateRoleSignal',
                array( 'roleId' => $roleId )
            ),
            array(
                'updateRole',
                array( $role, $roleUpdateStruct ),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdateRoleSignal',
                array( 'roleId' => $roleId )
            ),
            array(
                'addPolicy',
                array( $role, $policyCreateStruct ),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\AddPolicySignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $roleId
                )
            ),
            array(
                'removePolicy',
                array( $role, $policy ),
                $role,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicySignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $policyId
                )
            ),
            array(
                'deletePolicy',
                array( $policy ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicySignal',
                array(
                    'roleId' => $roleId,
                    'policyId' => $policyId
                )
            ),
            array(
                'updatePolicy',
                array( $policy, $policyUpdateStruct ),
                $policy,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdatePolicySignal',
                array( 'policyId' => $policyId )
            ),
            array(
                'loadRole',
                array( $roleId ),
                $role,
                0,
            ),
            array(
                'loadRoleByIdentifier',
                array( $roleIdentifier ),
                $role,
                0,
            ),
            array(
                'loadRoles',
                array(),
                array( $role ),
                0,
            ),
            array(
                'deleteRole',
                array( $role ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\DeleteRoleSignal',
                array( 'roleId' => $roleId )
            ),
            array(
                'loadPoliciesByUserId',
                array( $userId ),
                array( $policy ),
                0,
            ),
            array(
                'assignRoleToUserGroup',
                array( $role, $userGroup, $roleLimitation ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserGroupSignal',
                array(
                    'roleId' => $roleId,
                    'userGroupId' => $userGroupId,
                    'roleLimitation' => $roleLimitation
                )
            ),
            array(
                'unassignRoleFromUserGroup',
                array( $role, $userGroup ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserGroupSignal',
                array(
                    'roleId' => $roleId,
                    'userGroupId' => $userGroupId,
                )
            ),
            array(
                'assignRoleToUser',
                array( $role, $user, $roleLimitation ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserSignal',
                array(
                    'roleId' => $roleId,
                    'userId' => $userId,
                    'roleLimitation' => $roleLimitation
                )
            ),
            array(
                'unassignRoleFromUser',
                array( $role, $user ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserSignal',
                array(
                    'roleId' => $roleId,
                    'userId' => $userId,
                )
            ),
            array(
                'getRoleAssignments',
                array( $role ),
                array( $roleAssignement ),
                0
            ),
            array(
                'getRoleAssignmentsForUser',
                array( $user ),
                array( $roleAssignement ),
                0
            ),
            array(
                'getRoleAssignmentsForUserGroup',
                array( $userGroup ),
                array( $userGroupRoleAssignement ),
                0
            ),
            array(
                'newRoleCreateStruct',
                array( 'new role name' ),
                $roleCreateStruct,
                0
            ),
            array(
                'newPolicyCreateStruct',
                array( 'section', 'view' ),
                $policyCreateStruct,
                0
            ),
            array(
                'newPolicyUpdateStruct',
                array(),
                $policyUpdateStruct,
                0
            ),
            array(
                'newRoleUpdateStruct',
                array(),
                $roleUpdateStruct,
                0
            ),
            array(
                'getLimitationType',
                array( 'identifier' ),
                $this->getMock( 'eZ\\Publish\\SPI\\Limitation\\Type' ),
                0
            ),
            array(
                'getLimitationTypesByModuleFunction',
                array( 'module', 'function' ),
                array( $this->getMock( 'eZ\\Publish\\SPI\\Limitation\\Type' ) ),
                0
            ),
        );
    }
}
