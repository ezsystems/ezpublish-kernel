<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Events\Location\BeforeUpdateLocationEvent as BeforeUpdateLocationEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeUpdateLocationEvent extends BeforeEvent implements BeforeUpdateLocationEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct */
    private $locationUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location|null */
    private $updatedLocation;

    public function __construct(Location $location, LocationUpdateStruct $locationUpdateStruct)
    {
        $this->location = $location;
        $this->locationUpdateStruct = $locationUpdateStruct;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getLocationUpdateStruct(): LocationUpdateStruct
    {
        return $this->locationUpdateStruct;
    }

    public function getUpdatedLocation(): Location
    {
        if (!$this->hasUpdatedLocation()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedLocation() or set it by setUpdatedLocation() before you call getter.', Location::class));
        }

        return $this->updatedLocation;
    }

    public function setUpdatedLocation(?Location $updatedLocation): void
    {
        $this->updatedLocation = $updatedLocation;
    }

    public function hasUpdatedLocation(): bool
    {
        return $this->updatedLocation instanceof Location;
    }
}
