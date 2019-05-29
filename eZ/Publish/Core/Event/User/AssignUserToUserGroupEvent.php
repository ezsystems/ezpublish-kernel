<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Event\AfterEvent;

final class AssignUserToUserGroupEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.user.assign_to_user_group';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $user;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private $userGroup;

    public function __construct(
        User $user,
        UserGroup $userGroup
    ) {
        $this->user = $user;
        $this->userGroup = $userGroup;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }
}
