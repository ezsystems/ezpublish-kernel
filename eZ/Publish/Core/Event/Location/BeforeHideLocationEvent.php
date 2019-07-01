<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeHideLocationEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location|null */
    private $hiddenLocation;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getHiddenLocation(): Location
    {
        if (!$this->hasHiddenLocation()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasHiddenLocation() or set it by setHiddenLocation() before you call getter.', Location::class));
        }

        return $this->hiddenLocation;
    }

    public function setHiddenLocation(?Location $hiddenLocation): void
    {
        $this->hiddenLocation = $hiddenLocation;
    }

    public function hasHiddenLocation(): bool
    {
        return $this->hiddenLocation instanceof Location;
    }
}
