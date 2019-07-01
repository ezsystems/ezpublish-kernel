<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\Core\Event\AfterEvent;

final class CreateUserGroupEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct */
    private $userGroupCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\UserGroup */
    private $parentGroup;

    /** @var \eZ\Publish\API\Repository\Values\User\UserGroup */
    private $userGroup;

    public function __construct(
        UserGroup $userGroup,
        UserGroupCreateStruct $userGroupCreateStruct,
        UserGroup $parentGroup
    ) {
        $this->userGroupCreateStruct = $userGroupCreateStruct;
        $this->parentGroup = $parentGroup;
        $this->userGroup = $userGroup;
    }

    public function getUserGroupCreateStruct(): UserGroupCreateStruct
    {
        return $this->userGroupCreateStruct;
    }

    public function getParentGroup(): UserGroup
    {
        return $this->parentGroup;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }
}
