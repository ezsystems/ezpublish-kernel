<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Events\User\MoveUserGroupEvent as MoveUserGroupEventInterface;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class MoveUserGroupEvent extends AfterEvent implements MoveUserGroupEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\UserGroup */
    private $userGroup;

    /** @var \eZ\Publish\API\Repository\Values\User\UserGroup */
    private $newParent;

    public function __construct(
        UserGroup $userGroup,
        UserGroup $newParent
    ) {
        $this->userGroup = $userGroup;
        $this->newParent = $newParent;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getNewParent(): UserGroup
    {
        return $this->newParent;
    }
}
