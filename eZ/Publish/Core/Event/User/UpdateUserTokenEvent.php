<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Events\User\UpdateUserTokenEvent as UpdateUserTokenEventInterface;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateUserTokenEvent extends AfterEvent implements UpdateUserTokenEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $user;

    /** @var \eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct */
    private $userTokenUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $updatedUser;

    public function __construct(
        User $updatedUser,
        User $user,
        UserTokenUpdateStruct $userTokenUpdateStruct
    ) {
        $this->user = $user;
        $this->userTokenUpdateStruct = $userTokenUpdateStruct;
        $this->updatedUser = $updatedUser;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserTokenUpdateStruct(): UserTokenUpdateStruct
    {
        return $this->userTokenUpdateStruct;
    }

    public function getUpdatedUser(): User
    {
        return $this->updatedUser;
    }
}
