<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Location;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\Location;

interface BeforeHideLocationEvent extends BeforeEvent
{
    public function getLocation(): Location;

    public function getHiddenLocation(): Location;

    public function setHiddenLocation(?Location $hiddenLocation): void;

    public function hasHiddenLocation(): bool;
}
