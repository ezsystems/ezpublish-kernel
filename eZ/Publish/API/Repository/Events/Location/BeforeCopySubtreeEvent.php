<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Location;

use eZ\Publish\API\Repository\Values\Content\Location;

interface BeforeCopySubtreeEvent
{
    public function getSubtree(): Location;

    public function getTargetParentLocation(): Location;

    public function getLocation(): Location;

    public function setLocation(?Location $location): void;

    public function hasLocation(): bool;
}
