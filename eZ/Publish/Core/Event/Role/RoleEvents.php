<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

final class RoleEvents
{
    public const CREATE_ROLE = CreateRoleEvent::NAME;
    public const BEFORE_CREATE_ROLE = BeforeCreateRoleEvent::NAME;
    public const CREATE_ROLE_DRAFT = CreateRoleDraftEvent::NAME;
    public const BEFORE_CREATE_ROLE_DRAFT = BeforeCreateRoleDraftEvent::NAME;
    public const UPDATE_ROLE_DRAFT = UpdateRoleDraftEvent::NAME;
    public const BEFORE_UPDATE_ROLE_DRAFT = BeforeUpdateRoleDraftEvent::NAME;
    public const ADD_POLICY_BY_ROLE_DRAFT = AddPolicyByRoleDraftEvent::NAME;
    public const BEFORE_ADD_POLICY_BY_ROLE_DRAFT = BeforeAddPolicyByRoleDraftEvent::NAME;
    public const REMOVE_POLICY_BY_ROLE_DRAFT = RemovePolicyByRoleDraftEvent::NAME;
    public const BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT = BeforeRemovePolicyByRoleDraftEvent::NAME;
    public const UPDATE_POLICY_BY_ROLE_DRAFT = UpdatePolicyByRoleDraftEvent::NAME;
    public const BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT = BeforeUpdatePolicyByRoleDraftEvent::NAME;
    public const DELETE_ROLE_DRAFT = DeleteRoleDraftEvent::NAME;
    public const BEFORE_DELETE_ROLE_DRAFT = BeforeDeleteRoleDraftEvent::NAME;
    public const PUBLISH_ROLE_DRAFT = PublishRoleDraftEvent::NAME;
    public const BEFORE_PUBLISH_ROLE_DRAFT = BeforePublishRoleDraftEvent::NAME;
    public const UPDATE_ROLE = UpdateRoleEvent::NAME;
    public const BEFORE_UPDATE_ROLE = BeforeUpdateRoleEvent::NAME;
    public const ADD_POLICY = AddPolicyEvent::NAME;
    public const BEFORE_ADD_POLICY = BeforeAddPolicyEvent::NAME;
    public const DELETE_POLICY = DeletePolicyEvent::NAME;
    public const BEFORE_DELETE_POLICY = BeforeDeletePolicyEvent::NAME;
    public const UPDATE_POLICY = UpdatePolicyEvent::NAME;
    public const BEFORE_UPDATE_POLICY = BeforeUpdatePolicyEvent::NAME;
    public const DELETE_ROLE = DeleteRoleEvent::NAME;
    public const BEFORE_DELETE_ROLE = BeforeDeleteRoleEvent::NAME;
    public const ASSIGN_ROLE_TO_USER_GROUP = AssignRoleToUserGroupEvent::NAME;
    public const BEFORE_ASSIGN_ROLE_TO_USER_GROUP = BeforeAssignRoleToUserGroupEvent::NAME;
    public const UNASSIGN_ROLE_FROM_USER_GROUP = UnassignRoleFromUserGroupEvent::NAME;
    public const BEFORE_UNASSIGN_ROLE_FROM_USER_GROUP = BeforeUnassignRoleFromUserGroupEvent::NAME;
    public const ASSIGN_ROLE_TO_USER = AssignRoleToUserEvent::NAME;
    public const BEFORE_ASSIGN_ROLE_TO_USER = BeforeAssignRoleToUserEvent::NAME;
    public const UNASSIGN_ROLE_FROM_USER = UnassignRoleFromUserEvent::NAME;
    public const BEFORE_UNASSIGN_ROLE_FROM_USER = BeforeUnassignRoleFromUserEvent::NAME;
    public const REMOVE_ROLE_ASSIGNMENT = RemoveRoleAssignmentEvent::NAME;
    public const BEFORE_REMOVE_ROLE_ASSIGNMENT = BeforeRemoveRoleAssignmentEvent::NAME;
}
