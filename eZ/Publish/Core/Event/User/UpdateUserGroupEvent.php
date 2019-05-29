<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\Event\AfterEvent;

final class UpdateUserGroupEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.user_group.update';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private $userGroup;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    private $userGroupUpdateStruct;

    private $updatedUserGroup;

    public function __construct(
        UserGroup $updatedUserGroup,
        UserGroup $userGroup,
        UserGroupUpdateStruct $userGroupUpdateStruct
    ) {
        $this->userGroup = $userGroup;
        $this->userGroupUpdateStruct = $userGroupUpdateStruct;
        $this->updatedUserGroup = $updatedUserGroup;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getUserGroupUpdateStruct(): UserGroupUpdateStruct
    {
        return $this->userGroupUpdateStruct;
    }

    public function getUpdatedUserGroup(): UserGroup
    {
        return $this->updatedUserGroup;
    }
}
