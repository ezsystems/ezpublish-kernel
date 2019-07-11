<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

use eZ\Publish\API\Repository\Events\Location\BeforeCopySubtreeEvent as BeforeCopySubtreeEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforeCopySubtreeEvent extends Event implements BeforeCopySubtreeEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $subtree;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $targetParentLocation;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location|null */
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
