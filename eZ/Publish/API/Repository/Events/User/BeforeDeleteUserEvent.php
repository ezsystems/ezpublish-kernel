<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeDeleteUserEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $user;

    /** @var array|null */
    private $locations;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLocations(): array
    {
        if (!$this->hasLocations()) {
            throw new UnexpectedValueException('If you use stopPropagation(), you must set the event return value to be an array using setLocations()');
        }

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
