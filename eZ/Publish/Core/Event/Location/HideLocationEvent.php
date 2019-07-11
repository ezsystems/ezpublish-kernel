<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Events\Location\HideLocationEvent as HideLocationEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use Symfony\Contracts\EventDispatcher\Event;

final class HideLocationEvent extends Event implements HideLocationEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $hiddenLocation;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    public function __construct(
        Location $hiddenLocation,
        Location $location
    ) {
        $this->location = $location;
        $this->hiddenLocation = $hiddenLocation;
    }

    public function getHiddenLocation(): Location
    {
        return $this->hiddenLocation;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}
