<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\AfterEvent;

final class CopySubtreeEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $subtree;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $targetParentLocation;

    public function __construct(
        Location $location,
        Location $subtree,
        Location $targetParentLocation
    ) {
        $this->location = $location;
        $this->subtree = $subtree;
        $this->targetParentLocation = $targetParentLocation;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getSubtree(): Location
    {
        return $this->subtree;
    }

    public function getTargetParentLocation(): Location
    {
        return $this->targetParentLocation;
    }
}
