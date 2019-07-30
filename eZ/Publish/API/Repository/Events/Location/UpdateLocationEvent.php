<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Location;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateLocationEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $updatedLocation;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct */
    private $locationUpdateStruct;

    public function __construct(
        Location $updatedLocation,
        Location $location,
        LocationUpdateStruct $locationUpdateStruct
    ) {
        $this->updatedLocation = $updatedLocation;
        $this->location = $location;
        $this->locationUpdateStruct = $locationUpdateStruct;
    }

    public function getUpdatedLocation(): Location
    {
        return $this->updatedLocation;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getLocationUpdateStruct(): LocationUpdateStruct
    {
        return $this->locationUpdateStruct;
    }
}
