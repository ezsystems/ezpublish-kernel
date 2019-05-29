<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateUserEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.user.create.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserCreateStruct
     */
    private $userCreateStruct;

    /**
     * @var array
     */
    private $parentGroups;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User|null
     */
    private $user;

    public function __construct(UserCreateStruct $userCreateStruct, array $parentGroups)
    {
        $this->userCreateStruct = $userCreateStruct;
        $this->parentGroups = $parentGroups;
    }

    public function getUserCreateStruct(): UserCreateStruct
    {
        return $this->userCreateStruct;
    }

    public function getParentGroups(): array
    {
        return $this->parentGroups;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function hasUser(): bool
    {
        return $this->user instanceof User;
    }
}
