<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeUpdateUserTokenEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.user.token_update.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $user;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct
     */
    private $userTokenUpdateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User|null
     */
    private $updatedUser;

    public function __construct(User $user, UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        $this->user = $user;
        $this->userTokenUpdateStruct = $userTokenUpdateStruct;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserTokenUpdateStruct(): UserTokenUpdateStruct
    {
        return $this->userTokenUpdateStruct;
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
