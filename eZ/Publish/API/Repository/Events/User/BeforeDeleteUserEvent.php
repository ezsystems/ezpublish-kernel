<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\User\User;

interface BeforeDeleteUserEvent extends BeforeEvent
{
    public function getUser(): User;

    public function getLocations(): array;

    public function setLocations(?array $locations): void;

    public function hasLocations(): bool;
}
