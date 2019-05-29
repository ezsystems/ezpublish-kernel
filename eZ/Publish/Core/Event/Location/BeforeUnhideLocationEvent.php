<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeUnhideLocationEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.location.unhide.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location|null
     */
    private $revealedLocation;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getRevealedLocation(): ?Location
    {
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
