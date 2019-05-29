<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\AfterEvent;

final class MoveSubtreeEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.location.move_subtree';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $newParentLocation;

    public function __construct(
        Location $location,
        Location $newParentLocation
    ) {
        $this->location = $location;
        $this->newParentLocation = $newParentLocation;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getNewParentLocation(): Location
    {
        return $this->newParentLocation;
    }
}
