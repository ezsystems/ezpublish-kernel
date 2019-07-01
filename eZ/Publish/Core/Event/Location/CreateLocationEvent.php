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
use eZ\Publish\Core\Event\AfterEvent;

final class CreateLocationEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct */
    private $locationCreateStruct;

    public function __construct(
        Location $location,
        ContentInfo $contentInfo,
        LocationCreateStruct $locationCreateStruct
    ) {
        $this->location = $location;
        $this->contentInfo = $contentInfo;
        $this->locationCreateStruct = $locationCreateStruct;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getLocationCreateStruct(): LocationCreateStruct
    {
        return $this->locationCreateStruct;
    }
}
