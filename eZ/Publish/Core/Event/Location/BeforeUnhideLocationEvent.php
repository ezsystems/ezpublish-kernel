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

final class BeforeUnhideLocationEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location|null */
    private $revealedLocation;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getRevealedLocation(): Location
    {
        if (!$this->hasRevealedLocation()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasRevealedLocation() or set it by setRevealedLocation() before you call getter.', Location::class));
        }

        return $this->revealedLocation;
    }

    public function setRevealedLocation(?Location $revealedLocation): void
    {
        $this->revealedLocation = $revealedLocation;
    }

    public function hasRevealedLocation(): bool
    {
        return $this->revealedLocation instanceof Location;
    }
}
