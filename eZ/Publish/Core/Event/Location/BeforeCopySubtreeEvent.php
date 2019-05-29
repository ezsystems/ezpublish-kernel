<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCopySubtreeEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.location.copy_subtree.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $subtree;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $targetParentLocation;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location|null
     */
    private $location;

    public function __construct(Location $subtree, Location $targetParentLocation)
    {
        $this->subtree = $subtree;
        $this->targetParentLocation = $targetParentLocation;
    }

    public function getSubtree(): Location
    {
        return $this->subtree;
    }

    public function getTargetParentLocation(): Location
    {
        return $this->targetParentLocation;
    }

    public function getLocation(): ?Location
    {
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
