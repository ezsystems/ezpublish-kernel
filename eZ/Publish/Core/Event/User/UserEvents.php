<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

final class UserEvents
{
    public const CREATE_USER_GROUP = CreateUserGroupEvent::NAME;
    public const BEFORE_CREATE_USER_GROUP = BeforeCreateUserGroupEvent::NAME;
    public const DELETE_USER_GROUP = DeleteUserGroupEvent::NAME;
    public const BEFORE_DELETE_USER_GROUP = BeforeDeleteUserGroupEvent::NAME;
    public const MOVE_USER_GROUP = MoveUserGroupEvent::NAME;
    public const BEFORE_MOVE_USER_GROUP = BeforeMoveUserGroupEvent::NAME;
    public const UPDATE_USER_GROUP = UpdateUserGroupEvent::NAME;
    public const BEFORE_UPDATE_USER_GROUP = BeforeUpdateUserGroupEvent::NAME;
    public const CREATE_USER = CreateUserEvent::NAME;
    public const BEFORE_CREATE_USER = BeforeCreateUserEvent::NAME;
    public const DELETE_USER = DeleteUserEvent::NAME;
    public const BEFORE_DELETE_USER = BeforeDeleteUserEvent::NAME;
    public const UPDATE_USER = UpdateUserEvent::NAME;
    public const BEFORE_UPDATE_USER = BeforeUpdateUserEvent::NAME;
    public const UPDATE_USER_TOKEN = UpdateUserTokenEvent::NAME;
    public const BEFORE_UPDATE_USER_TOKEN = BeforeUpdateUserTokenEvent::NAME;
    public const ASSIGN_USER_TO_USER_GROUP = AssignUserToUserGroupEvent::NAME;
    public const BEFORE_ASSIGN_USER_TO_USER_GROUP = BeforeAssignUserToUserGroupEvent::NAME;
    public const UN_ASSIGN_USER_FROM_USER_GROUP = UnAssignUserFromUserGroupEvent::NAME;
    public const BEFORE_UN_ASSIGN_USER_FROM_USER_GROUP = BeforeUnAssignUserFromUserGroupEvent::NAME;
}
