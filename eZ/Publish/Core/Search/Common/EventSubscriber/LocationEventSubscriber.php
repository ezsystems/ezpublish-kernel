<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\EventSubscriber;

use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent;
use eZ\Publish\API\Repository\Events\Section\AssignSectionToSubtreeEvent;
use eZ\Publish\API\Repository\Values\Content\Location;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocationEventSubscriber extends AbstractSearchEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AssignSectionToSubtreeEvent::class => 'onAssignSectionToSubtree',
            CopySubtreeEvent::class => 'onCopySubtree',
            CreateLocationEvent::class => 'onCreateLocation',
            DeleteLocationEvent::class => 'onDeleteLocation',
            HideLocationEvent::class => 'onHideLocation',
            MoveSubtreeEvent::class => 'onMoveSubtree',
            SwapLocationEvent::class => 'onSwapLocation',
            UnhideLocationEvent::class => 'onUnhideLocation',
            UpdateLocationEvent::class => 'onUpdateLocation',
        ];
    }

    public function onCopySubtree(CopySubtreeEvent $event)
    {
        $this->indexSubtree($event->getLocation()->id);
    }

    public function onCreateLocation(CreateLocationEvent $event)
    {
        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo(
            $event->getContentInfo()->id
        );

        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $contentInfo->id,
                $contentInfo->currentVersionNo
            )
        );
    }

    public function onDeleteLocation(DeleteLocationEvent $event)
    {
        $this->searchHandler->deleteLocation(
            $event->getLocation()->id,
            $event->getLocation()->contentId
        );
    }

    public function onHideLocation(HideLocationEvent $event)
    {
        $this->indexSubtree($event->getHiddenLocation()->id);
    }

    public function onMoveSubtree(MoveSubtreeEvent $event)
    {
        $this->indexSubtree($event->getLocation()->id);
    }

    public function onSwapLocation(SwapLocationEvent $event)
    {
        $locations = [
            $event->getLocation1(),
            $event->getLocation2(),
        ];

        array_walk($locations, function (Location $location) {
            $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($location->contentId);

            $this->searchHandler->indexContent(
                $this->persistenceHandler->contentHandler()->load(
                    $location->contentId,
                    $contentInfo->currentVersionNo
                )
            );

            $this->searchHandler->indexLocation(
                $this->persistenceHandler->locationHandler()->load($location->id)
            );
        });
    }

    public function onUnhideLocation(UnhideLocationEvent $event)
    {
        $this->indexSubtree($event->getRevealedLocation()->id);
    }

    public function onUpdateLocation(UpdateLocationEvent $event)
    {
        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo(
            $event->getLocation()->contentId
        );

        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load(
                $event->getLocation()->contentId,
                $contentInfo->currentVersionNo
            )
        );

        $this->searchHandler->indexLocation(
            $this->persistenceHandler->locationHandler()->load(
                $event->getLocation()->id
            )
        );
    }

    public function onAssignSectionToSubtree(AssignSectionToSubtreeEvent $event): void
    {
        $this->indexSubtree($event->getLocation()->id);
    }
}
