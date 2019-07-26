<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Location;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;

interface BeforeUpdateLocationEvent
{
    public function getLocation(): Location;

    public function getLocationUpdateStruct(): LocationUpdateStruct;

    public function getUpdatedLocation(): Location;

    public function setUpdatedLocation(?Location $updatedLocation): void;

    public function hasUpdatedLocation(): bool;
}
