<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent as SwapLocationEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use Symfony\Contracts\EventDispatcher\Event;

final class SwapLocationEvent extends Event implements SwapLocationEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location1;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location2;

    public function __construct(
        Location $location1,
        Location $location2
    ) {
        $this->location1 = $location1;
        $this->location2 = $location2;
    }

    public function getLocation1(): Location
    {
        return $this->location1;
    }

    public function getLocation2(): Location
    {
        return $this->location2;
    }
}
