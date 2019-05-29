<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeUpdateUserEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.user.update.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $user;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    private $userUpdateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User|null
     */
    private $updatedUser;

    public function __construct(User $user, UserUpdateStruct $userUpdateStruct)
    {
        $this->user = $user;
        $this->userUpdateStruct = $userUpdateStruct;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserUpdateStruct(): UserUpdateStruct
    {
        return $this->userUpdateStruct;
    }

    public function getUpdatedUser(): ?User
    {
        return $this->updatedUser;
    }

    public function setUpdatedUser(?User $updatedUser): void
    {
        $this->updatedUser = $updatedUser;
    }

    public function hasUpdatedUser(): bool
    {
        return $this->updatedUser instanceof User;
    }
}
