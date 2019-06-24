<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\Core\Event\AfterEvent;

final class CreateUserEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserCreateStruct
     */
    private $userCreateStruct;

    /**
     * @var array
     */
    private $parentGroups;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $user;

    public function __construct(
        User $user,
        UserCreateStruct $userCreateStruct,
        array $parentGroups
    ) {
        $this->userCreateStruct = $userCreateStruct;
        $this->parentGroups = $parentGroups;
        $this->user = $user;
    }

    public function getUserCreateStruct(): UserCreateStruct
    {
        return $this->userCreateStruct;
    }

    public function getParentGroups(): array
    {
        return $this->parentGroups;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
