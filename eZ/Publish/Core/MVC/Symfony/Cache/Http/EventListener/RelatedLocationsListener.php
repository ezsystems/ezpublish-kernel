<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\EventListener;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds locations from related content to the Http cache clear list, for given content.
 * Both relation and reverse relation are taken into account.
 */
class RelatedLocationsListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->contentService = $repository->getContentService();
        $this->locationService = $repository->getLocationService();
    }

    public static function getSubscribedEvents()
    {
        return [MVCEvents::CACHE_CLEAR_CONTENT => ['onContentCacheClear', 100]];
    }

    public function onContentCacheClear(ContentCacheClearEvent $event)
    {
        $contentInfo = $event->getContentInfo();
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo);

        foreach ($this->contentService->loadRelations($versionInfo) as $relation) {
            foreach ($this->locationService->loadLocations($relation->getDestinationContentInfo()) as $relatedLocation) {
                $event->addLocationToClear($relatedLocation);
            }
        }

        // Using sudo since loading reverse relations is conditioned to content/reverserelatedlist permission and we don't need this check here.
        /** @var \eZ\Publish\API\Repository\Values\Content\Relation[] $reverseRelations */
        $reverseRelations = $this->repository->sudo(
            function () use ($contentInfo) {
                return $this->contentService->loadReverseRelations($contentInfo);
            }
        );
        foreach ($reverseRelations as $reverseRelation) {
            foreach ($this->locationService->loadLocations($reverseRelation->getSourceContentInfo()) as $relatedLocation) {
                $event->addLocationToClear($relatedLocation);
            }
        }
    }
}
