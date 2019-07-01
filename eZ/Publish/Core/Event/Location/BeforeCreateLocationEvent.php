<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateLocationEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct */
    private $locationCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location|null */
    private $location;

    public function __construct(ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct)
    {
        $this->contentInfo = $contentInfo;
        $this->locationCreateStruct = $locationCreateStruct;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getLocationCreateStruct(): LocationCreateStruct
    {
        return $this->locationCreateStruct;
    }

    public function getLocation(): Location
    {
        if (!$this->hasLocation()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasLocation() or set it by setLocation() before you call getter.', Location::class));
        }

        return $this->location;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    public function hasLocation(): bool
    {
        return $this->location instanceof Location;
    }
}
