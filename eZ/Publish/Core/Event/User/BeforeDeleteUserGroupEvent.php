<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeDeleteUserGroupEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private $userGroup;

    /**
     * @var array|null
     */
    private $locations;

    public function __construct(UserGroup $userGroup)
    {
        $this->userGroup = $userGroup;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getLocations(): ?array
    {
        return $this->locations;
    }

    public function setLocations(?array $locations): void
    {
        $this->locations = $locations;
    }

    public function hasLocations(): bool
    {
        return is_array($this->locations);
    }
}
