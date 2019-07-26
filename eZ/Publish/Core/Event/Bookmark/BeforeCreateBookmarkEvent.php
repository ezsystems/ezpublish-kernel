<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Bookmark;

use eZ\Publish\API\Repository\Events\Bookmark\BeforeCreateBookmarkEvent as BeforeCreateBookmarkEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;

final class BeforeCreateBookmarkEvent extends BeforeEvent implements BeforeCreateBookmarkEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}
