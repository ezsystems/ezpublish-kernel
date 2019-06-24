<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\LocationServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\Core\Event\Location\BeforeCopySubtreeEvent;
use eZ\Publish\Core\Event\Location\BeforeCreateLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeDeleteLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeHideLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeMoveSubtreeEvent;
use eZ\Publish\Core\Event\Location\BeforeSwapLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeUnhideLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeUpdateLocationEvent;
use eZ\Publish\Core\Event\Location\CopySubtreeEvent;
use eZ\Publish\Core\Event\Location\CreateLocationEvent;
use eZ\Publish\Core\Event\Location\DeleteLocationEvent;
use eZ\Publish\Core\Event\Location\HideLocationEvent;
use eZ\Publish\Core\Event\Location\MoveSubtreeEvent;
use eZ\Publish\Core\Event\Location\SwapLocationEvent;
use eZ\Publish\Core\Event\Location\UnhideLocationEvent;
use eZ\Publish\Core\Event\Location\UpdateLocationEvent;

class LocationService extends LocationServiceDecorator
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        LocationServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function copySubtree(
        Location $subtree,
        Location $targetParentLocation
    ): Location {
        $eventData = [
            $subtree,
            $targetParentLocation,
        ];

        $beforeEvent = new BeforeCopySubtreeEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getLocation();
        }

        $location = $beforeEvent->hasLocation()
            ? $beforeEvent->getLocation()
            : parent::copySubtree($subtree, $targetParentLocation);

        $this->eventDispatcher->dispatch(new CopySubtreeEvent($location, ...$eventData));

        return $location;
    }

    public function createLocation(
        ContentInfo $contentInfo,
        LocationCreateStruct $locationCreateStruct
    ): Location {
        $eventData = [
            $contentInfo,
            $locationCreateStruct,
        ];

        $beforeEvent = new BeforeCreateLocationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getLocation();
        }

        $location = $beforeEvent->hasLocation()
            ? $beforeEvent->getLocation()
            : parent::createLocation($contentInfo, $locationCreateStruct);

        $this->eventDispatcher->dispatch(new CreateLocationEvent($location, ...$eventData));

        return $location;
    }

    public function updateLocation(
        Location $location,
        LocationUpdateStruct $locationUpdateStruct
    ): Location {
        $eventData = [
            $location,
            $locationUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateLocationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUpdatedLocation();
        }

        $updatedLocation = $beforeEvent->hasUpdatedLocation()
            ? $beforeEvent->getUpdatedLocation()
            : parent::updateLocation($location, $locationUpdateStruct);

        $this->eventDispatcher->dispatch(new UpdateLocationEvent($updatedLocation, ...$eventData));

        return $updatedLocation;
    }

    public function swapLocation(
        Location $location1,
        Location $location2
    ): void {
        $eventData = [
            $location1,
            $location2,
        ];

        $beforeEvent = new BeforeSwapLocationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::swapLocation($location1, $location2);

        $this->eventDispatcher->dispatch(new SwapLocationEvent(...$eventData));
    }

    public function hideLocation(Location $location): Location
    {
        $eventData = [$location];

        $beforeEvent = new BeforeHideLocationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getHiddenLocation();
        }

        $hiddenLocation = $beforeEvent->hasHiddenLocation()
            ? $beforeEvent->getHiddenLocation()
            : parent::hideLocation($location);

        $this->eventDispatcher->dispatch(new HideLocationEvent($hiddenLocation, ...$eventData));

        return $hiddenLocation;
    }

    public function unhideLocation(Location $location): Location
    {
        $eventData = [$location];

        $beforeEvent = new BeforeUnhideLocationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getRevealedLocation();
        }

        $revealedLocation = $beforeEvent->hasRevealedLocation()
            ? $beforeEvent->getRevealedLocation()
            : parent::unhideLocation($location);

        $this->eventDispatcher->dispatch(new UnhideLocationEvent($revealedLocation, ...$eventData));

        return $revealedLocation;
    }

    public function moveSubtree(
        Location $location,
        Location $newParentLocation
    ): void {
        $eventData = [
            $location,
            $newParentLocation,
        ];

        $beforeEvent = new BeforeMoveSubtreeEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::moveSubtree($location, $newParentLocation);

        $this->eventDispatcher->dispatch(new MoveSubtreeEvent(...$eventData));
    }

    public function deleteLocation(Location $location): void
    {
        $eventData = [$location];

        $beforeEvent = new BeforeDeleteLocationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteLocation($location);

        $this->eventDispatcher->dispatch(new DeleteLocationEvent(...$eventData));
    }
}
