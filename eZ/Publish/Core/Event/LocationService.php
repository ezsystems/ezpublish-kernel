<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\Events\Location\BeforeCopySubtreeEvent as BeforeCopySubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeCreateLocationEvent as BeforeCreateLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeDeleteLocationEvent as BeforeDeleteLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeHideLocationEvent as BeforeHideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeMoveSubtreeEvent as BeforeMoveSubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeSwapLocationEvent as BeforeSwapLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeUnhideLocationEvent as BeforeUnhideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeUpdateLocationEvent as BeforeUpdateLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent as CopySubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent as CreateLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent as DeleteLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent as HideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent as MoveSubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent as SwapLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent as UnhideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent as UpdateLocationEventInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Events\Location\BeforeCopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeCreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeDeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeHideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeMoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeSwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeUnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeUpdateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent;
use eZ\Publish\SPI\Repository\Decorator\LocationServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LocationService extends LocationServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCopySubtreeEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocation();
        }

        $location = $beforeEvent->hasLocation()
            ? $beforeEvent->getLocation()
            : $this->innerService->copySubtree($subtree, $targetParentLocation);

        $this->eventDispatcher->dispatch(
            new CopySubtreeEvent($location, ...$eventData),
            CopySubtreeEventInterface::class
        );

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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateLocationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocation();
        }

        $location = $beforeEvent->hasLocation()
            ? $beforeEvent->getLocation()
            : $this->innerService->createLocation($contentInfo, $locationCreateStruct);

        $this->eventDispatcher->dispatch(
            new CreateLocationEvent($location, ...$eventData),
            CreateLocationEventInterface::class
        );

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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateLocationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedLocation();
        }

        $updatedLocation = $beforeEvent->hasUpdatedLocation()
            ? $beforeEvent->getUpdatedLocation()
            : $this->innerService->updateLocation($location, $locationUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateLocationEvent($updatedLocation, ...$eventData),
            UpdateLocationEventInterface::class
        );

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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeSwapLocationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->swapLocation($location1, $location2);

        $this->eventDispatcher->dispatch(
            new SwapLocationEvent(...$eventData),
            SwapLocationEventInterface::class
        );
    }

    public function hideLocation(Location $location): Location
    {
        $eventData = [$location];

        $beforeEvent = new BeforeHideLocationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeHideLocationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getHiddenLocation();
        }

        $hiddenLocation = $beforeEvent->hasHiddenLocation()
            ? $beforeEvent->getHiddenLocation()
            : $this->innerService->hideLocation($location);

        $this->eventDispatcher->dispatch(
            new HideLocationEvent($hiddenLocation, ...$eventData),
            HideLocationEventInterface::class
        );

        return $hiddenLocation;
    }

    public function unhideLocation(Location $location): Location
    {
        $eventData = [$location];

        $beforeEvent = new BeforeUnhideLocationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUnhideLocationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getRevealedLocation();
        }

        $revealedLocation = $beforeEvent->hasRevealedLocation()
            ? $beforeEvent->getRevealedLocation()
            : $this->innerService->unhideLocation($location);

        $this->eventDispatcher->dispatch(
            new UnhideLocationEvent($revealedLocation, ...$eventData),
            UnhideLocationEventInterface::class
        );

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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeMoveSubtreeEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->moveSubtree($location, $newParentLocation);

        $this->eventDispatcher->dispatch(
            new MoveSubtreeEvent(...$eventData),
            MoveSubtreeEventInterface::class
        );
    }

    public function deleteLocation(Location $location): void
    {
        $eventData = [$location];

        $beforeEvent = new BeforeDeleteLocationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteLocationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteLocation($location);

        $this->eventDispatcher->dispatch(
            new DeleteLocationEvent(...$eventData),
            DeleteLocationEventInterface::class
        );
    }
}
