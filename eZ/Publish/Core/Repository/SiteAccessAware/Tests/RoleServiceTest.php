<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\RoleService as APIService;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\RoleService;
use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\Core\Repository\Values\User\PolicyDraft;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\Core\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\Core\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;

class RoleServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return RoleService::class;
    }

    public function providerForPassTroughMethods()
    {
        $roleCreateStruct = new RoleCreateStruct();
        $roleUpdateStruct = new RoleUpdateStruct();
        $role = new Role();
        $roleDraft = new RoleDraft();

        $policyCreateStruct = new PolicyCreateStruct();
        $policyUpdateStruct = new PolicyUpdateStruct();
        $policyDraft = new PolicyDraft();
        $policy = new Policy();

        $roleLimitation = new SubtreeLimitation();

        $userGroup = new UserGroup();
        $user = new User();

        $roleAssignment = new UserRoleAssignment();

        // string $method, array $arguments, bool $return = true
        return [
            ['createRole', [$roleCreateStruct]],
            ['createRoleDraft', [$role]],
            ['loadRoleDraft', [24]],
            ['loadRoleDraftByRoleId', [24]],
            ['updateRoleDraft', [$roleDraft, $roleUpdateStruct]],
            ['addPolicyByRoleDraft', [$roleDraft, $policyCreateStruct]],
            ['removePolicyByRoleDraft', [$roleDraft, $policyDraft]],
            ['updatePolicyByRoleDraft', [$roleDraft, $policyDraft, $policyUpdateStruct]],
            ['deleteRoleDraft', [$roleDraft]],
            ['publishRoleDraft', [$roleDraft]],
            ['updateRole', [$role, $roleUpdateStruct]],
            ['addPolicy', [$role, $policyCreateStruct]],
            ['deletePolicy', [$policy]],
            ['updatePolicy', [$policy, $policyUpdateStruct]],
            ['loadRole', [24]],
            ['loadRoleByIdentifier', ['admin']],
            ['loadRoles', []],
            ['deleteRole', [$role]],
            ['loadPoliciesByUserId', [10]],
            ['assignRoleToUserGroup', [$role, $userGroup, $roleLimitation]],
            ['unassignRoleFromUserGroup', [$role, $userGroup]],
            ['assignRoleToUser', [$role, $user, $roleLimitation]],
            ['unassignRoleFromUser', [$role, $user]],
            ['removeRoleAssignment', [$roleAssignment]],
            ['loadRoleAssignment', [55]],
            ['getRoleAssignments', [$role]],
            ['getRoleAssignmentsForUser', [$user, true]],
            ['getRoleAssignmentsForUserGroup', [$userGroup]],
            ['newRoleCreateStruct', ['Editor']],
            ['newPolicyCreateStruct', ['content', 'edit']],
            ['newPolicyUpdateStruct', []],
            ['newRoleUpdateStruct', []],
            ['getLimitationType', ['subtree']],
            ['getLimitationTypesByModuleFunction', ['content', 'edit']],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
