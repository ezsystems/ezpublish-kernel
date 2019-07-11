<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Events\User\BeforeUpdateUserGroupEvent as BeforeUpdateUserGroupEventInterface;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforeUpdateUserGroupEvent extends Event implements BeforeUpdateUserGroupEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\UserGroup */
    private $userGroup;

    /** @var \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct */
    private $userGroupUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\UserGroup|null */
    private $updatedUserGroup;

    public function __construct(UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        $this->userGroup = $userGroup;
        $this->userGroupUpdateStruct = $userGroupUpdateStruct;
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
        if (!$this->hasUpdatedUserGroup()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedUserGroup() or set it by setUpdatedUserGroup() before you call getter.', UserGroup::class));
        }

        return $this->updatedUserGroup;
    }

    public function setUpdatedUserGroup(?UserGroup $updatedUserGroup): void
    {
        $this->updatedUserGroup = $updatedUserGroup;
    }

    public function hasUpdatedUserGroup(): bool
    {
        return $this->updatedUserGroup instanceof UserGroup;
    }
}
